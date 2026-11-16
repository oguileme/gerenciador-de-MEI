<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import type { Quote, SaveQuotePayload } from '@/types/quote'
import { formatMoney } from '@/utils/format'
import {
  calculateQuoteSubtotal,
  normalizeQuoteMoney,
  normalizeQuoteQuantity,
  sumQuoteSubtotals,
} from '@/utils/quoteMath'

interface EditableItem {
  key: number
  description: string
  unitPrice: string
  quantity: string
}

const props = defineProps<{
  meiName: string
  quote: Quote | null
  submitting: boolean
  serverErrors: Record<string, string>
}>()
const emit = defineEmits<{ close: []; save: [payload: SaveQuotePayload] }>()
const dialog = ref<HTMLDialogElement | null>(null)
const recipientName = ref(props.quote?.recipient_name ?? '')
let nextKey = 1
const items = ref<EditableItem[]>(props.quote?.items?.map((item) => ({
  key: nextKey++,
  description: item.description,
  unitPrice: item.unit_price.replace('.', ','),
  quantity: item.quantity.replace('.', ',').replace(/,?0+$/, ''),
})) ?? [emptyItem()])
const localErrors = ref<Record<string, string>>({})
const subtotals = computed(() => items.value.map((item) => (
  calculateQuoteSubtotal(item.unitPrice, item.quantity)
)))
const total = computed(() => sumQuoteSubtotals(subtotals.value))

onMounted(() => dialog.value?.showModal())

function emptyItem(): EditableItem {
  return { key: nextKey++, description: '', unitPrice: '', quantity: '1' }
}

function addItem() {
  items.value.push(emptyItem())
  const lastItem = items.value[items.value.length - 1]
  void nextTick(() => dialog.value?.querySelector<HTMLInputElement>(
    `[data-item-key="${lastItem?.key}"]`,
  )?.focus())
}

function removeItem(index: number) {
  if (items.value.length > 1) items.value.splice(index, 1)
}

function errorFor(field: string): string {
  return localErrors.value[field] ?? props.serverErrors[field] ?? ''
}

async function submit() {
  const errors: Record<string, string> = {}
  if (!recipientName.value.trim()) errors.recipient_name = 'Informe quem receberá o orçamento.'
  const normalizedItems: SaveQuotePayload['items'] = []

  items.value.forEach((item, index) => {
    const unitPrice = normalizeQuoteMoney(item.unitPrice)
    const quantity = normalizeQuoteQuantity(item.quantity)
    if (!item.description.trim()) errors[`items.${index}.description`] = 'Descreva o serviço.'
    if (!unitPrice) errors[`items.${index}.unit_price`] = 'Informe um valor maior que zero.'
    if (!quantity) errors[`items.${index}.quantity`] = 'Informe uma quantidade maior que zero.'
    if (item.description.trim() && unitPrice && quantity) {
      normalizedItems.push({ description: item.description.trim(), unit_price: unitPrice, quantity })
    }
  })

  localErrors.value = errors
  if (Object.keys(errors).length) {
    await nextTick()
    dialog.value?.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus()
    return
  }
  emit('save', { recipient_name: recipientName.value.trim(), items: normalizedItems })
}
</script>

<template>
  <dialog
    ref="dialog" class="quote-dialog" aria-labelledby="quote-form-heading"
    @cancel="submitting ? $event.preventDefault() : emit('close')"
  >
    <form class="quote-sheet" @submit.prevent="submit">
      <header class="sheet-heading">
        <div>
          <p>{{ quote ? `Orçamento #${quote.id}` : 'Novo orçamento' }}</p>
          <h2 id="quote-form-heading">{{ quote ? 'Ajustar proposta' : 'Preparar proposta' }}</h2>
        </div>
        <button class="close-button" type="button" :disabled="submitting" aria-label="Fechar" @click="emit('close')">×</button>
      </header>

      <div class="document-meta">
        <div><span>Emitente</span><strong>{{ quote?.issuer_name ?? meiName }}</strong></div>
        <div><span>Data</span><strong>{{ quote ? quote.issue_date.split('-').reverse().join('/') : 'Definida ao registrar' }}</strong></div>
        <div><span>Status inicial</span><strong>{{ quote ? 'Pendente' : 'Pendente' }}</strong></div>
      </div>

      <div class="recipient-field">
        <label for="quote-recipient">Quem receberá este orçamento?</label>
        <input
          id="quote-recipient" v-model="recipientName" maxlength="255" autocomplete="organization"
          placeholder="Nome do cliente ou empresa" :disabled="submitting" required
          :aria-invalid="!!errorFor('recipient_name')"
          :aria-describedby="errorFor('recipient_name') ? 'quote-recipient-error' : undefined"
        >
        <span v-if="errorFor('recipient_name')" id="quote-recipient-error" class="field-error">{{ errorFor('recipient_name') }}</span>
      </div>

      <section class="service-lines" aria-labelledby="services-heading">
        <div class="service-heading">
          <div><h3 id="services-heading">Serviços</h3><p>O subtotal acompanha cada linha.</p></div>
          <button class="add-service" type="button" :disabled="submitting" @click="addItem">+ Adicionar serviço</button>
        </div>

        <div class="line-labels" aria-hidden="true">
          <span>Descrição</span><span>Valor unitário</span><span>Quantidade</span><span>Subtotal</span><span></span>
        </div>
        <div v-for="(item, index) in items" :key="item.key" class="service-line">
          <div class="line-field description-field">
            <label :for="`quote-description-${item.key}`">Descrição do serviço {{ index + 1 }}</label>
            <input
              :id="`quote-description-${item.key}`" v-model="item.description"
              :data-item-key="item.key" maxlength="1000" placeholder="Ex.: Criação de identidade visual"
              :disabled="submitting" :aria-invalid="!!errorFor(`items.${index}.description`)"
            >
            <span v-if="errorFor(`items.${index}.description`)" class="field-error">{{ errorFor(`items.${index}.description`) }}</span>
          </div>
          <div class="line-field">
            <label :for="`quote-unit-${item.key}`">Valor unitário</label>
            <div class="money-input"><span>R$</span><input
              :id="`quote-unit-${item.key}`" v-model="item.unitPrice" inputmode="decimal"
              maxlength="11" placeholder="0,00" :disabled="submitting"
              :aria-invalid="!!errorFor(`items.${index}.unit_price`)"
            ></div>
            <span v-if="errorFor(`items.${index}.unit_price`)" class="field-error">{{ errorFor(`items.${index}.unit_price`) }}</span>
          </div>
          <div class="line-field">
            <label :for="`quote-quantity-${item.key}`">Quantidade</label>
            <input
              :id="`quote-quantity-${item.key}`" v-model="item.quantity" inputmode="decimal"
              maxlength="7" :disabled="submitting"
              :aria-invalid="!!errorFor(`items.${index}.quantity`)"
            >
            <span v-if="errorFor(`items.${index}.quantity`)" class="field-error">{{ errorFor(`items.${index}.quantity`) }}</span>
          </div>
          <output class="line-subtotal" :for="`quote-unit-${item.key} quote-quantity-${item.key}`">
            <span>Subtotal</span><strong>{{ subtotals[index] ? formatMoney(subtotals[index]) : '—' }}</strong>
          </output>
          <button
            class="remove-service" type="button" :disabled="submitting || items.length === 1"
            :aria-label="`Remover serviço ${index + 1}`" @click="removeItem(index)"
          >×</button>
        </div>
      </section>

      <footer class="sheet-total">
        <div><span>Valor total previsto</span><strong>{{ total ? formatMoney(total) : 'Preencha todos os valores' }}</strong><small>O servidor confirma o total ao registrar.</small></div>
        <div class="form-actions">
          <button class="button-secondary" type="button" :disabled="submitting" @click="emit('close')">Cancelar</button>
          <button class="button-primary" type="submit" :disabled="submitting">{{ submitting ? 'Registrando…' : quote ? 'Salvar alterações' : 'Registrar orçamento' }}</button>
        </div>
      </footer>
    </form>
  </dialog>
</template>

<style scoped>
.quote-dialog {
  width: min(1040px, calc(100% - 32px));
  max-height: calc(100svh - 32px);
  padding: 0;
  border: 1px solid var(--color-border);
  border-radius: 18px;
  color: var(--color-text);
  background: var(--color-paper);
  box-shadow: 0 24px 80px var(--color-shadow);
}

.quote-dialog::backdrop { background: var(--color-overlay); }

.quote-sheet { padding: 30px; }

.sheet-heading, .service-heading, .sheet-total, .form-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
}

.sheet-heading { padding-bottom: 22px; border-bottom: 1px solid var(--color-border); }
.sheet-heading p, .service-heading p { margin: 0 0 4px; color: var(--color-muted); font-size: 12px; }
h2, h3 { margin: 0; letter-spacing: -.5px; }
h2 { font-size: 26px; }
h3 { font-size: 17px; }

.close-button, .remove-service {
  display: grid;
  place-items: center;
  border: 0;
  background: transparent;
  color: var(--color-muted);
}
.close-button { width: 42px; height: 42px; font-size: 28px; }

.document-meta {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr;
  gap: 1px;
  margin: 22px 0;
  overflow: hidden;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-border);
}
.document-meta div { padding: 14px 16px; background: var(--color-surface-soft); }
.document-meta span, .line-subtotal span { display: block; color: var(--color-muted); font-size: 10px; }
.document-meta strong { display: block; margin-top: 3px; font-size: 12px; overflow-wrap: anywhere; }

.recipient-field { display: grid; gap: 7px; max-width: 620px; margin-bottom: 30px; }
label { font-size: 12px; font-weight: 700; }
input {
  width: 100%;
  min-height: 44px;
  padding: 10px 12px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-text);
  background: var(--color-paper);
}
input[aria-invalid='true'] { border-color: var(--color-error); }

.service-lines { border-top: 2px solid var(--color-forest); }
.service-heading { padding: 18px 0; }
.add-service { border: 0; background: transparent; color: var(--color-forest); font-weight: 700; }
.line-labels, .service-line {
  display: grid;
  grid-template-columns: minmax(230px, 1fr) 150px 110px 140px 34px;
  gap: 12px;
  align-items: start;
}
.line-labels { padding: 9px 12px; color: var(--color-muted); background: var(--color-surface-soft); font-size: 10px; }
.service-line { padding: 14px 12px; border-bottom: 1px solid var(--color-grid); }
.line-field { display: grid; gap: 5px; min-width: 0; }
.line-field label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); }
.money-input { display: grid; grid-template-columns: auto 1fr; align-items: center; border: 1px solid var(--color-border); border-radius: 8px; }
.money-input:focus-within { outline: 3px solid var(--color-focus); outline-offset: 2px; }
.money-input span { padding-left: 10px; color: var(--color-muted); font-size: 11px; }
.money-input input { border: 0; outline: 0; }
.line-subtotal { min-height: 44px; padding: 5px 0; text-align: right; }
.line-subtotal strong { display: block; margin-top: 3px; font-variant-numeric: tabular-nums; }
.remove-service { width: 34px; height: 44px; font-size: 22px; }
.remove-service:disabled { opacity: .25; cursor: default; }
.field-error { color: var(--color-error); font-size: 10px; line-height: 1.35; }

.sheet-total { align-items: flex-end; padding-top: 24px; }
.sheet-total > div:first-child { margin-left: auto; text-align: right; }
.sheet-total span, .sheet-total small { display: block; color: var(--color-muted); font-size: 11px; }
.sheet-total strong { display: block; margin: 3px 0; font-size: 24px; font-variant-numeric: tabular-nums; }
.form-actions { margin-left: 32px; }

@media (max-width: 760px) {
  .quote-sheet { padding: 22px 18px; }
  .document-meta { grid-template-columns: 1fr; }
  .line-labels { display: none; }
  .service-line { grid-template-columns: 1fr 1fr 36px; gap: 10px; padding: 18px 0; }
  .description-field { grid-column: 1 / -1; }
  .line-field label { position: static; width: auto; height: auto; overflow: visible; clip: auto; }
  .line-subtotal { grid-column: 1 / 3; text-align: left; }
  .remove-service { grid-column: 3; grid-row: 2; }
  .sheet-total { align-items: stretch; flex-direction: column; }
  .sheet-total > div:first-child { margin: 0; text-align: left; }
  .form-actions { width: 100%; margin: 8px 0 0; }
  .form-actions button { flex: 1; }
}
</style>
