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
const dragging = ref(false)
let dragDepth = 0
const labels: Record<InvoiceStatus, string> = {
  active: 'Ativa', cancelled: 'Cancelada', substituted: 'Substituída',
}
onMounted(() => load())

function openImport() {
  if (!importing.value) fileInput.value?.click()
}
async function handleFiles(event: Event) {
  const input = event.target as HTMLInputElement
  await importFiles(Array.from(input.files ?? []))
  input.value = ''
}
function handleDragEnter() {
  if (importing.value) return
  dragDepth++
  dragging.value = true
}
function handleDragLeave() {
  if (importing.value) return
  dragDepth = Math.max(0, dragDepth - 1)
  if (dragDepth === 0) dragging.value = false
}
function handleDragOver(event: DragEvent) {
  if (!importing.value && event.dataTransfer) event.dataTransfer.dropEffect = 'copy'
}
async function handleDrop(event: DragEvent) {
  dragDepth = 0
  dragging.value = false
  if (importing.value) return
  await importFiles(Array.from(event.dataTransfer?.files ?? []))
}
function changeStatus(invoice: Invoice, event: Event) {
  const input = event.target as HTMLSelectElement
  const value = input.value as InvoiceStatus
  input.value = invoice.status
  void updateStatus(invoice, value)
}
defineExpose({ openImport })
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
      </div>
    </div>
    <div
      class="upload-dropzone" :class="{ dragging, disabled: importing }" role="button"
      :tabindex="importing ? -1 : 0" :aria-disabled="importing"
      aria-label="Importar arquivos XML de NFS-e"
      @click="openImport" @keydown.enter.prevent="openImport" @keydown.space.prevent="openImport"
      @dragenter.prevent="handleDragEnter" @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop"
    >
      <span class="upload-icon" aria-hidden="true">↑</span>
      <span><strong>{{ importing ? 'Importando XMLs…' : 'Arraste seus XMLs aqui' }}</strong>
        <small>{{ importing ? 'Aguarde o processamento dos arquivos.' : 'ou clique para procurar nos arquivos' }}</small>
      </span>
    </div>
    <input ref="fileInput" class="file-input" type="file" accept=".xml,application/xml,text/xml" multiple aria-label="Arquivos XML de NFS-e" :disabled="importing" @change="handleFiles">
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
  background: var(--color-paper);
  border: 1px solid var(--color-border);
  padding: 9px 12px;
  border-radius: 8px;
  min-height: 40px;
}

.file-input {
  display: none;
}

.upload-dropzone {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  min-height: 112px;
  margin-top: 22px;
  padding: 20px;
  border: 2px dashed var(--color-border);
  border-radius: 14px;
  color: var(--color-forest);
  background: var(--color-surface-soft);
  text-align: left;
  cursor: pointer;
  transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
}

.upload-dropzone:hover, .upload-dropzone:focus-visible, .upload-dropzone.dragging {
  border-color: var(--color-forest);
  background: var(--color-sage);
}

.upload-dropzone:focus-visible {
  outline: 3px solid color-mix(in srgb, var(--color-focus) 25%, transparent);
  outline-offset: 3px;
}

.upload-dropzone.dragging {
  transform: translateY(-2px);
}

.upload-dropzone.disabled {
  opacity: .65;
  cursor: wait;
}

.upload-dropzone strong, .upload-dropzone small {
  display: block;
}

.upload-dropzone small {
  margin-top: 4px;
  color: var(--color-muted);
  font-size: 12px;
}

.upload-icon {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  flex: 0 0 auto;
  border-radius: 10px;
  background: var(--color-surface-raised);
  border: 1px solid var(--color-border);
  font-size: 22px;
  font-weight: 700;
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
  background: var(--color-surface-soft);
}

td {
  padding: 17px 12px;
  border-bottom: 1px solid var(--color-grid);
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
  background: var(--color-error-soft);
}

.substituted {
  color: var(--color-warning);
  background: var(--color-warning-soft);
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
  background: var(--color-surface-soft);
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
  .upload-dropzone {
    min-height: 104px;
    padding: 18px 14px;
  }
}
</style>
