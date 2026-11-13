<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import type { Mei } from '@/types/mei'
import { useDashboardSettings } from '../composables/useDashboardSettings'

const props = defineProps<{ mei: Mei; year: number; amount: string; mode: 'mei' | 'limit' }>()
const emit = defineEmits<{ close: []; saved: [mei?: Mei] }>()
const dialog = ref<HTMLDialogElement | null>(null)
const { values, errors, error, submitting, save } = useDashboardSettings(props.mei, props.year, props.amount, props.mode)
const fields = [
  { name: 'mei_name' as const, label: 'Nome do MEI', type: 'text', maxlength: 255 },
  { name: 'opening_date' as const, label: 'Data de abertura', type: 'date' },
  { name: 'ibge_municipality_code' as const, label: 'Código IBGE do município', type: 'text', maxlength: 7, pattern: '[0-9]{7}' },
]
onMounted(() => dialog.value?.showModal())
async function submit() {
  const result = await save()
  if (result) { emit('saved', result === true ? undefined : result); return }
  await nextTick()
  dialog.value?.querySelector<HTMLInputElement>('[aria-invalid="true"]')?.focus()
}
</script>

<template>
  <dialog ref="dialog" aria-labelledby="settings-heading" @cancel="submitting ? $event.preventDefault() : emit('close')">
    <form @submit.prevent="submit">
      <h2 id="settings-heading">{{ mode === 'mei' ? 'Dados do MEI' : `Limite anual de ${year}` }}</h2>
      <template v-if="mode === 'mei'">
        <p class="hint">CNPJ {{ mei.cnpj }}. O CNPJ é mantido para preservar o vínculo com as notas importadas.</p>
        <div v-for="field in fields" :key="field.name" class="field">
          <label :for="`settings-${field.name}`">{{ field.label }}</label>
          <input :id="`settings-${field.name}`" v-model="values[field.name]" :type="field.type" :maxlength="field.maxlength" :pattern="field.pattern"
            :inputmode="field.pattern ? 'numeric' : undefined" :disabled="submitting" required
            :aria-invalid="!!errors[field.name]" :aria-describedby="errors[field.name] ? `error-${field.name}` : undefined">
          <span v-if="errors[field.name]" :id="`error-${field.name}`" class="field-error">{{ errors[field.name] }}</span>
        </div>
      </template>
      <template v-else>
        <p class="hint">Defina o valor que deseja acompanhar neste ano. Alterar esse valor não muda o teto legal do MEI.</p>
        <div class="field"><label for="settings-amount">Limite anual (R$)</label>
          <input id="settings-amount" v-model="values.amount" inputmode="decimal" maxlength="16" :disabled="submitting" required :aria-invalid="!!errors.amount" aria-describedby="amount-help">
          <span id="amount-help" :class="errors.amount ? 'field-error' : 'hint'">{{ errors.amount || 'Exemplo: 81000,00. O valor não será reduzido no ano de abertura.' }}</span>
        </div>
      </template>
      <p v-if="error" role="alert" class="field-error">{{ error }}</p>
      <div class="actions"><button class="button-secondary" type="button" :disabled="submitting" @click="emit('close')">Cancelar</button><button class="button-primary" type="submit" :disabled="submitting">{{ submitting ? 'Salvando…' : 'Salvar alterações' }}</button></div>
    </form>
  </dialog>
</template>

<style scoped>
dialog {
  width: min(470px, calc(100% - 32px));
  max-height: calc(100svh - 40px);
  border: 1px solid var(--color-border);
  border-radius: 22px;
  padding: 30px;
  color: var(--color-text);
  background: white;
}

dialog::backdrop {
  background: #18332466;
}

h2 {
  margin: 0 0 12px;
  font-size: 24px;
  letter-spacing: -.6px;
}

.hint {
  color: var(--color-muted);
  font-size: 12px;
}

.field {
  display: grid;
  gap: 7px;
  margin-top: 20px;
}

label {
  font-size: 13px;
  font-weight: 700;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  color: var(--color-text);
  background: white;
}

input[aria-invalid='true'] {
  border-color: var(--color-error);
}

.field-error {
  color: var(--color-error);
  font-size: 12px;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 28px;
}
</style>
