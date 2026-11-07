<script setup lang="ts">
import { computed, ref } from 'vue'
import AuthIcon from './AuthIcon.vue'

/** Campo rotulado com erro acessível e alternância local da visibilidade de senha. */
const props = withDefaults(defineProps<{
  id: string
  label: string
  type?: 'text' | 'email' | 'password'
  autocomplete: string
  placeholder: string
  icon: 'email' | 'lock' | 'user'
  error?: string
  maxlength?: number
}>(), { type: 'text', error: '' })

const model = defineModel<string>({ required: true })
defineEmits<{ blur: [] }>()
const passwordVisible = ref(false)
const inputType = computed(() => props.type === 'password' && passwordVisible.value
  ? 'text'
  : props.type)
</script>

<template>
  <div class="field">
    <label :for="id">{{ label }}</label>
    <div class="input-wrap" :class="{ 'has-error': error }">
      <AuthIcon class="input-icon" :name="icon" />
      <input
        :id="id"
        v-model="model"
        :name="id"
        :type="inputType"
        :autocomplete="autocomplete"
        :placeholder="placeholder"
        :maxlength="maxlength"
        :aria-invalid="error ? true : undefined"
        :aria-describedby="error ? `${id}-error` : undefined"
        :spellcheck="false"
        :autocapitalize="type === 'text' ? 'words' : 'none'"
        required
        @blur="$emit('blur')"
      >
      <button
        v-if="type === 'password'"
        class="password-toggle"
        type="button"
        :aria-label="`${passwordVisible ? 'Ocultar' : 'Mostrar'} ${label.toLowerCase()}`"
        :aria-pressed="passwordVisible"
        :aria-controls="id"
        @click="passwordVisible = !passwordVisible"
      >
        <AuthIcon :name="passwordVisible ? 'eye-off' : 'eye'" />
      </button>
    </div>
    <p v-if="error" :id="`${id}-error`" class="field-error">{{ error }}</p>
  </div>
</template>

<style scoped>
.field label {
  display: block;
  margin-bottom: 8px;
  font-size: 12px;
  font-weight: 700;
}
.input-wrap {
  display: flex;
  min-height: 51px;
  align-items: center;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  background: #fdfefc;
}
.input-wrap:focus-within {
  border-color: var(--color-focus);
  box-shadow: 0 0 0 3px #39795514;
}
.input-wrap.has-error {
  border-color: var(--color-error);
}
.input-icon {
  width: 17px;
  height: 17px;
  margin-left: 15px;
  color: #879187;
}
input {
  width: 100%;
  min-width: 0;
  align-self: stretch;
  padding: 12px;
  border: 0;
  border-radius: inherit;
  outline: none;
  color: var(--color-text);
  background: transparent;
  font-size: 13px;
}
input::placeholder {
  color: #7d867f;
  opacity: 1;
}
.password-toggle {
  display: grid;
  width: 44px;
  height: 44px;
  flex: 0 0 auto;
  place-items: center;
  margin-right: 3px;
  padding: 0;
  border: 0;
  border-radius: 7px;
  color: var(--color-muted);
  background: transparent;
}
.password-toggle:hover {
  color: var(--color-forest);
  background: var(--color-sage);
}
.password-toggle svg {
  width: 18px;
  height: 18px;
}
.field-error {
  margin: 6px 0 0;
  color: var(--color-error);
  font-size: 11px;
  line-height: 1.6;
}
@media (max-width: 760px) {
  input {
    font-size: 16px;
  }
}
</style>
