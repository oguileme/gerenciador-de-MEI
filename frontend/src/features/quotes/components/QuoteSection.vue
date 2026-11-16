<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { Quote, QuoteStatus, SaveQuotePayload } from '@/types/quote'
import { formatDate, formatMoney } from '@/utils/format'
import { useQuotes } from '../composables/useQuotes'
import QuoteFormDialog from './QuoteFormDialog.vue'

const props = defineProps<{ meiId: string; meiName: string }>()
const {
  quotes, page, lastPage, total, status, loading, busy, loadingDetail,
  error, notice, fieldErrors, load, loadDetail, save, changeStatus, remove, download,
} = useQuotes(props.meiId)
const expanded = ref<number | null>(null)
const formOpen = ref(false)
const formQuote = ref<Quote | null>(null)
const labels: Record<QuoteStatus, string> = {
  pending: 'Pendente', accepted: 'Aceito', rejected: 'Negado',
}

onMounted(() => load())

function openNew() {
  fieldErrors.value = {}
  formQuote.value = null
  formOpen.value = true
}

async function openEdit(quote: Quote) {
  const detailed = await loadDetail(quote)
  if (!detailed || detailed.status !== 'pending') return
  fieldErrors.value = {}
  formQuote.value = detailed
  formOpen.value = true
}

async function toggleDetails(quote: Quote) {
  if (expanded.value === quote.id) { expanded.value = null; return }
  const detailed = await loadDetail(quote)
  if (detailed) expanded.value = quote.id
}

async function submit(payload: SaveQuotePayload) {
  if (await save(payload, formQuote.value?.id)) formOpen.value = false
}

function allowedStatuses(quote: Quote): QuoteStatus[] {
  return quote.status === 'pending' ? ['pending', 'accepted', 'rejected'] : [quote.status, 'pending']
}

async function selectStatus(quote: Quote, event: Event) {
  const select = event.target as HTMLSelectElement
  const next = select.value as QuoteStatus
  select.value = quote.status
  await changeStatus(quote, next)
}

async function removeQuote(quote: Quote) {
  if (!window.confirm(`Excluir o orçamento de ${quote.recipient_name}?`)) return
  if (await remove(quote) && expanded.value === quote.id) expanded.value = null
}

async function downloadPdf(quote: Quote) {
  const blob = await download(quote)
  if (!blob) return
  const url = URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = url
  anchor.download = `orcamento-${quote.id}.pdf`
  anchor.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <section id="quotes" class="quote-section" aria-labelledby="quotes-heading">
    <header class="quote-heading">
      <div><h2 id="quotes-heading">Orçamentos</h2><p>{{ total }} {{ total === 1 ? 'proposta registrada' : 'propostas registradas' }}</p></div>
      <button class="button-primary" @click="openNew"><span aria-hidden="true">+</span> Novo orçamento</button>
    </header>

    <div class="quote-toolbar" aria-label="Filtrar orçamentos por status">
      <button v-for="(label, value) in { '': 'Todos', ...labels }" :key="value" :class="{ active: status === value }" @click="status = value as QuoteStatus | ''; load(1)">{{ label }}</button>
    </div>

    <p v-if="notice" class="quote-notice" role="status">{{ notice }}</p>
    <p v-if="loading" class="quote-empty" role="status">Abrindo o livro de orçamentos…</p>
    <div v-else-if="error" class="quote-empty" role="alert"><p>{{ error }}</p><button class="button-secondary" @click="load()">Tentar novamente</button></div>
    <div v-else-if="!quotes.length" class="quote-empty">
      <h3>{{ status ? 'Nenhum orçamento com este status.' : 'Sua próxima proposta começa aqui.' }}</h3>
      <p>{{ status ? 'Escolha outro status para consultar o histórico.' : 'Registre os serviços, confira o total e baixe o PDF para o cliente.' }}</p>
      <button v-if="!status" class="button-primary" @click="openNew">Preparar primeiro orçamento</button>
    </div>

    <div v-else class="quote-ledger">
      <article v-for="quote in quotes" :key="quote.id" class="quote-entry">
        <div class="quote-row">
          <button class="quote-identity" :aria-expanded="expanded === quote.id" :aria-controls="`quote-detail-${quote.id}`" @click="toggleDetails(quote)">
            <span class="quote-number">#{{ quote.id }}</span>
            <span><strong>{{ quote.recipient_name }}</strong><small>Emitido por {{ quote.issuer_name }}</small></span>
          </button>
          <span class="quote-date">{{ formatDate(quote.issue_date) }}</span>
          <strong class="quote-amount">{{ formatMoney(quote.total_amount) }}</strong>
          <select
            :value="quote.status" :class="['quote-status', quote.status]" :disabled="busy"
            :aria-label="`Status do orçamento ${quote.id}`" @change="selectStatus(quote, $event)"
          ><option v-for="option in allowedStatuses(quote)" :key="option" :value="option">{{ labels[option] }}</option></select>
          <button class="open-detail" @click="toggleDetails(quote)">{{ loadingDetail === quote.id ? 'Abrindo…' : expanded === quote.id ? 'Fechar' : 'Ver proposta' }}</button>
        </div>

        <div v-if="expanded === quote.id && quote.items" :id="`quote-detail-${quote.id}`" class="quote-detail">
          <div class="detail-services">
            <div v-for="item in quote.items" :key="item.id" class="detail-line">
              <span>{{ item.description }}</span>
              <small>{{ item.quantity.replace('.', ',') }} × {{ formatMoney(item.unit_price) }}</small>
              <strong>{{ formatMoney(item.subtotal) }}</strong>
            </div>
          </div>
          <div class="detail-actions">
            <button class="button-secondary" @click="downloadPdf(quote)">Baixar PDF</button>
            <button v-if="quote.status === 'pending'" class="text-action" @click="openEdit(quote)">Editar</button>
            <button v-if="quote.status === 'pending'" class="text-action danger-action" @click="removeQuote(quote)">Excluir</button>
          </div>
        </div>
      </article>
    </div>

    <nav v-if="lastPage > 1 && !loading && !error" class="pagination" aria-label="Páginas de orçamentos">
      <button class="button-secondary" :disabled="page === 1" @click="load(page - 1)">Anterior</button>
      <span>{{ page }} de {{ lastPage }}</span>
      <button class="button-secondary" :disabled="page === lastPage" @click="load(page + 1)">Próxima</button>
    </nav>

    <QuoteFormDialog
      v-if="formOpen" :key="formQuote?.id ?? 'new'" :mei-name="meiName" :quote="formQuote"
      :submitting="busy" :server-errors="fieldErrors" @close="formOpen = false" @save="submit"
    />
  </section>
</template>

<style scoped>
.quote-section {
  margin-top: 26px;
  padding: 26px;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  background: var(--color-paper);
}
.quote-heading, .quote-row, .detail-line, .detail-actions, .pagination {
  display: flex;
  align-items: center;
}
.quote-heading { justify-content: space-between; gap: 18px; }
h2, h3, p { margin-top: 0; }
h2 { margin-bottom: 4px; font-size: 18px; letter-spacing: -.4px; }
.quote-heading p { margin-bottom: 0; color: var(--color-muted); font-size: 12px; }

.quote-toolbar { display: flex; gap: 4px; margin: 22px 0 8px; border-bottom: 1px solid var(--color-border); }
.quote-toolbar button { padding: 10px 13px; border: 0; border-bottom: 2px solid transparent; background: transparent; color: var(--color-muted); font-size: 11px; }
.quote-toolbar button.active { border-bottom-color: var(--color-forest); color: var(--color-forest); font-weight: 700; }

.quote-notice { margin: 14px 0; padding: 10px 14px; border-radius: 8px; color: var(--color-forest); background: var(--color-sage); font-size: 12px; }
.quote-empty { padding: 52px 16px; text-align: center; color: var(--color-muted); }
.quote-empty h3 { color: var(--color-text); }
.quote-empty p { margin-bottom: 18px; }

.quote-ledger { border-top: 1px solid var(--color-border); }
.quote-entry { border-bottom: 1px solid var(--color-border); }
.quote-row { display: grid; grid-template-columns: minmax(240px, 1fr) 100px 140px 120px 100px; gap: 16px; min-height: 78px; padding: 10px 8px; }
.quote-identity { display: flex; align-items: center; gap: 13px; min-width: 0; padding: 0; border: 0; background: transparent; color: var(--color-text); text-align: left; }
.quote-identity strong, .quote-identity small { display: block; overflow-wrap: anywhere; }
.quote-identity small { margin-top: 3px; color: var(--color-muted); font-size: 10px; }
.quote-number { display: grid; place-items: center; min-width: 46px; height: 38px; border: 1px solid var(--color-border); border-radius: 7px; color: var(--color-forest); font-size: 11px; font-weight: 800; }
.quote-date { color: var(--color-muted); font-size: 11px; }
.quote-amount { text-align: right; font-variant-numeric: tabular-nums; }
.quote-status { min-height: 36px; padding: 6px 8px; border: 1px solid var(--color-border); border-radius: 7px; font: inherit; font-size: 11px; }
.quote-status.pending { color: var(--color-warning); background: var(--color-warning-soft); }
.quote-status.accepted { color: var(--color-forest); background: var(--color-sage); }
.quote-status.rejected { color: var(--color-error); background: var(--color-error-soft); }
.open-detail, .text-action { border: 0; background: transparent; color: var(--color-forest); font-size: 11px; text-decoration: underline; }

.quote-detail { padding: 0 8px 18px 67px; }
.detail-services { background: var(--color-surface-soft); border-left: 3px solid var(--color-leaf); }
.detail-line { display: grid; grid-template-columns: minmax(0, 1fr) 190px 130px; gap: 16px; padding: 12px 16px; border-bottom: 1px solid var(--color-grid); font-size: 12px; }
.detail-line span { white-space: pre-wrap; overflow-wrap: anywhere; }
.detail-line small { color: var(--color-muted); text-align: right; }
.detail-line strong { text-align: right; font-variant-numeric: tabular-nums; }
.detail-actions { justify-content: flex-end; gap: 12px; margin-top: 14px; }
.danger-action { color: var(--color-error); }
.pagination { justify-content: flex-end; gap: 18px; margin-top: 20px; }

@media (max-width: 1000px) {
  .quote-row { grid-template-columns: minmax(220px, 1fr) 120px 115px 90px; }
  .quote-date { display: none; }
}
@media (max-width: 700px) {
  .quote-section { padding: 20px 16px; }
  .quote-heading { align-items: flex-start; flex-direction: column; }
  .quote-toolbar { overflow-x: auto; }
  .quote-row { grid-template-columns: 1fr auto; gap: 10px; padding: 16px 4px; }
  .quote-identity { grid-column: 1 / -1; }
  .quote-amount { text-align: left; }
  .quote-status { justify-self: end; }
  .open-detail { grid-column: 1 / -1; justify-self: start; padding: 7px 0; }
  .quote-detail { padding: 0 0 18px; }
  .detail-line { grid-template-columns: 1fr auto; }
  .detail-line small { grid-column: 1; text-align: left; }
  .detail-line strong { grid-column: 2; grid-row: 1 / 3; align-self: center; }
  .detail-actions { flex-wrap: wrap; justify-content: flex-start; }
}
</style>
