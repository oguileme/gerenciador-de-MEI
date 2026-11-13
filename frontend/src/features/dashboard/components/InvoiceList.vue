<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { Invoice, InvoiceStatus } from '@/types/invoice'
import { formatDate, formatMoney } from '@/utils/format'
import { useInvoices } from '../composables/useInvoices'

const props = defineProps<{ meiId: string; refreshMetrics: () => Promise<void> }>()
const { invoices, page, lastPage, total, status, loading, error, notice,
  updating, importing, uploads, load, updateStatus, importFiles } = useInvoices(props.meiId, props.refreshMetrics)
const expanded = ref<number | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const labels: Record<InvoiceStatus, string> = {
  active: 'Ativa', cancelled: 'Cancelada', substituted: 'Substituída',
}
onMounted(() => load())

async function handleFiles(event: Event) {
  const input = event.target as HTMLInputElement
  await importFiles(Array.from(input.files ?? []))
  input.value = ''
}
function changeStatus(invoice: Invoice, event: Event) {
  const input = event.target as HTMLSelectElement
  const value = input.value as InvoiceStatus
  input.value = invoice.status
  void updateStatus(invoice, value)
}
defineExpose({ openImport: () => fileInput.value?.click() })
</script>

<template>
  <section id="invoices" class="invoice-section" aria-labelledby="invoices-heading">
    <div class="section-heading">
      <div><h2 id="invoices-heading">Notas fiscais</h2><p>{{ total }} {{ total === 1 ? 'nota registrada' : 'notas registradas' }} · Todos os períodos</p></div>
      <div class="invoice-actions">
        <label class="filter">Status
          <select v-model="status" @change="load(1)">
            <option value="">Todos</option>
            <option v-for="(label, value) in labels" :key="value" :value="value">{{ label }}</option>
          </select>
        </label>
        <button class="button-secondary" :disabled="importing" @click="fileInput?.click()">
          {{ importing ? 'Importando…' : 'Importar XML' }}
        </button>
        <input ref="fileInput" class="file-input" type="file" accept=".xml,application/xml,text/xml" multiple aria-label="Arquivos XML de NFS-e" :disabled="importing" @change="handleFiles">
      </div>
    </div>
    <ul v-if="uploads.length" class="upload-results" aria-live="polite">
      <li v-for="(upload, index) in uploads" :key="index" :class="{ failed: !upload.success }">
        <strong>{{ upload.name }}</strong>: {{ upload.message }}
      </li>
    </ul>
    <p v-if="notice" role="status" class="notice">{{ notice }}</p>
    <p v-if="loading" role="status" class="empty">Carregando notas fiscais…</p>
    <div v-else-if="error" role="alert" class="empty"><p>{{ error }}</p><button class="button-secondary" @click="load()">Tentar novamente</button></div>
    <div v-else-if="!invoices.length" class="empty">
      <h3>{{ status ? 'Nenhuma nota com este status.' : 'Suas notas começam aqui.' }}</h3>
      <p>{{ status ? 'Escolha outro status para consultar suas notas.' : 'Importe o XML de uma NFS-e para acompanhar seu faturamento.' }}</p>
    </div>
    <div v-else class="table-scroll">
      <p class="scroll-hint">Deslize para ver o status e os detalhes das notas.</p>
      <table>
        <thead><tr><th scope="col">Nota / Tomador</th><th scope="col">Emissão</th><th scope="col">Valor bruto</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Detalhes</span></th></tr></thead>
        <tbody>
          <template v-for="invoice in invoices" :key="invoice.id">
            <tr>
              <td><strong>Nº {{ invoice.invoice_number }}</strong><span class="customer">{{ invoice.customer_name || 'Tomador não informado' }}</span></td>
              <td>{{ formatDate(invoice.issue_date) }}</td>
              <td class="amount">{{ formatMoney(invoice.service_amount) }}</td>
              <td><select :value="invoice.status" :aria-label="`Status da nota ${invoice.invoice_number}`" :class="['status-select', invoice.status]" :disabled="updating !== null" @change="changeStatus(invoice, $event)">
                <option v-for="(label, value) in labels" :key="value" :value="value">{{ label }}</option>
              </select></td>
              <td><button class="detail-button" :aria-expanded="expanded === invoice.id" :aria-controls="`invoice-${invoice.id}`" @click="expanded = expanded === invoice.id ? null : invoice.id">{{ expanded === invoice.id ? 'Fechar' : 'Ver nota' }}</button></td>
            </tr>
            <tr v-if="expanded === invoice.id" :id="`invoice-${invoice.id}`" class="details"><td colspan="5">
              <dl><dt>Competência</dt><dd>{{ formatDate(invoice.competence_date) }}</dd><dt>Valor líquido</dt><dd>{{ formatMoney(invoice.net_amount) }}</dd><dt>Chave de acesso</dt><dd>{{ invoice.access_key }}</dd><dt>Descrição do serviço</dt><dd>{{ invoice.description }}</dd></dl>
              <p>A mudança de status registra o acompanhamento aqui. Ela não cancela a nota no portal emissor.</p>
            </td></tr>
          </template>
        </tbody>
      </table>
    </div>
    <nav v-if="lastPage > 1 && !loading && !error" class="pagination" aria-label="Páginas de notas fiscais">
      <button class="button-secondary" :disabled="page === 1" @click="load(page - 1)">Anterior</button>
      <span>{{ page }} de {{ lastPage }}</span>
      <button class="button-secondary" :disabled="page === lastPage" @click="load(page + 1)">Próxima</button>
    </nav>
  </section>
</template>

<style scoped>
.invoice-section {
  background: var(--color-paper);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 26px;
}

.section-heading, .invoice-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

h2, h3 {
  margin: 0;
}

h2 {
  font-size: 18px;
  letter-spacing: -.4px;
}

.section-heading p {
  margin: 5px 0 0;
  font-size: 12px;
  color: var(--color-muted);
}

.filter {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

select {
  font: inherit;
  color: var(--color-text);
  background: white;
  border: 1px solid var(--color-border);
  padding: 9px 12px;
  border-radius: 8px;
  min-height: 40px;
}

.file-input {
  display: none;
}

.scroll-hint {
  display: none;
}

.table-scroll {
  position: relative;
  overflow-x: auto;
  margin-top: 22px;
}

table {
  min-width: 620px;
  border-collapse: collapse;
  width: 100%;
  text-align: left;
  font-size: 13px;
}

th {
  color: var(--color-muted);
  font-size: 11px;
  font-weight: 500;
  padding: 12px;
  background: #f7f9f6;
}

td {
  padding: 17px 12px;
  border-bottom: 1px solid #edf0e9;
}

.customer {
  display: block;
  font-size: 12px;
  color: var(--color-muted);
  margin-top: 3px;
  max-width: 240px;
  overflow-wrap: anywhere;
}

.amount {
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
}

.status-select {
  font-size: 11px;
  padding: 6px 10px;
  min-height: 36px;
}

.active {
  color: var(--color-forest);
  background: var(--color-sage);
}

.cancelled {
  color: var(--color-error);
  background: #faf0ee;
}

.substituted {
  color: #6b5b3c;
  background: #f7f2e7;
}

.detail-button {
  background: transparent;
  border: 0;
  color: var(--color-forest);
  padding: 10px;
  text-decoration: underline;
  white-space: nowrap;
}

.empty {
  padding: 40px 12px;
  text-align: center;
  color: var(--color-muted);
}

.upload-results, .notice {
  padding: 16px 20px;
  border-radius: 10px;
  background: var(--color-sage);
  font-size: 12px;
  overflow-wrap: anywhere;
}

.failed {
  color: var(--color-error);
}

.details {
  background: #f7f9f6;
}

dl {
  margin: 0;
  max-width: 650px;
  overflow-wrap: anywhere;
}

dt {
  font-size: 11px;
  color: var(--color-muted);
  margin-top: 12px;
}

dd {
  margin: 2px 0 0;
  white-space: pre-wrap;
}

.details p {
  font-size: 11px;
  color: var(--color-muted);
}

.pagination {
  display: flex;
  gap: 18px;
  align-items: center;
  justify-content: flex-end;
  margin-top: 20px;
  font-size: 12px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip-path: inset(50%);
}

@media (max-width: 700px) {
  .scroll-hint {
    display: block;
    font-size: 11px;
    color: var(--color-muted);
    margin: 0 0 14px;
  }
  .invoice-section {
    padding: 20px 16px;
  }
  .section-heading {
    align-items: flex-start;
    flex-direction: column;
  }
  .invoice-actions {
    width: 100%;
  }
}
</style>
