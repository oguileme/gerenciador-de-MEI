<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import type { CreateMei, Mei } from '@/types/mei'
import { useCreateMei } from '../composables/useCreateMei'

const emit = defineEmits<{ close: []; created: [mei: Mei] }>()
const dialog = ref<HTMLDialogElement | null>(null)
const { values, errors, error, submitting, submit } = useCreateMei()
const fields: { name: keyof CreateMei; label: string; hint?: string; type: string; pattern?: string; maxlength?: number }[] = [
  { name: 'mei_name', label: 'Nome do MEI', type: 'text', maxlength: 255 },
  { name: 'cnpj', label: 'CNPJ', type: 'text', pattern: '[0-9]{14}', maxlength: 14, hint: 'Informe os 14 dígitos, sem pontuação.' },
  { name: 'opening_date', label: 'Data de abertura', type: 'date' },
  { name: 'ibge_municipality_code', label: 'Código IBGE do município', type: 'text', pattern: '[0-9]{7}', maxlength: 7, hint: 'Código de 7 dígitos do município do MEI.' },
]
onMounted(() => dialog.value?.showModal())

async function handleSubmit() {
  const mei = await submit()
  if (mei) { emit('created', mei); return }
  await nextTick()
  dialog.value?.querySelector<HTMLInputElement>('[aria-invalid="true"]')?.focus()
}
</script>

<template>
  <dialog ref="dialog" aria-labelledby="create-mei-title" @cancel="submitting ? $event.preventDefault() : emit('close')">
    <form @submit.prevent="handleSubmit">
      <h2 id="create-mei-title">Adicionar MEI</h2>
      <p class="description">Vamos começar pelos dados do seu negócio.</p>
      <div v-for="field in fields" :key="field.name" class="field">
        <label :for="field.name">{{ field.label }}</label>
        <input
          :id="field.name" v-model="values[field.name]" :name="field.name"
          :type="field.type" :pattern="field.pattern" :maxlength="field.maxlength"
          :inputmode="field.pattern ? 'numeric' : undefined" :disabled="submitting"
          :aria-invalid="!!errors[field.name]"
          :aria-describedby="errors[field.name] ? `${field.name}-error` : field.hint ? `${field.name}-hint` : undefined"
          required
        >
        <span v-if="errors[field.name]" :id="`${field.name}-error`" class="error">{{ errors[field.name] }}</span>
        <span v-else-if="field.hint" :id="`${field.name}-hint`" class="hint">{{ field.hint }}</span>
      </div>
      <p v-if="error" role="alert" class="error">{{ error }}</p>
      <div class="actions">
        <button type="button" :disabled="submitting" @click="emit('close')">Cancelar</button>
        <button class="primary" type="submit" :disabled="submitting">{{ submitting ? 'Cadastrando…' : 'Cadastrar MEI' }}</button>
      </div>
    </form>
  </dialog>
</template>

<style scoped>
dialog {
  width: min(480px, calc(100% - 32px));
  max-height: calc(100svh - 48px);
  padding: 32px;
  border: 1px solid var(--color-border);
  border-radius: 24px;
  color: var(--color-text);
  background: var(--color-paper);
}

dialog::backdrop {
  background: #18332466;
}

h2 {
  margin: 0;
  font-size: 26px;
  letter-spacing: -0.8px;
}

.description {
  margin: 8px 0 24px;
  color: var(--color-muted);
}

.field {
  display: grid;
  gap: 6px;
  margin-bottom: 18px;
}

label {
  font-weight: 700;
  font-size: 13px;
}

input {
  width: 100%;
  min-width: 0;
  min-height: 46px;
  padding: 10px 12px;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  background: var(--color-paper);
  color: var(--color-text);
}

input[aria-invalid='true'] {
  border-color: var(--color-error);
}

.hint, .error {
  font-size: 12px;
}

.hint {
  color: var(--color-muted);
}

.error {
  color: var(--color-error);
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 26px;
}

button {
  min-height: 44px;
  padding: 10px 16px;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  background: var(--color-paper);
  color: var(--color-forest);
  font-weight: 700;
}

.primary {
  background: var(--color-forest);
  color: var(--color-paper);
  border-color: var(--color-forest);
}

button:disabled {
  opacity: .65;
  cursor: wait;
}

@media (max-width: 400px) {
  dialog {
    padding: 24px 20px;
  }
}
</style>
