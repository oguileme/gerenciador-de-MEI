<script setup lang="ts">
import { nextTick, ref } from 'vue'
import router from '../../../router'
import type { AuthMode } from '../types/auth'
import { useAuthForm } from '../composables/useAuthForm'
import AuthField from './AuthField.vue'
import AuthIcon from './AuthIcon.vue'

const props = defineProps<{ mode: AuthMode }>()
const { values, errors, notice, submitting, touch, submit } = useAuthForm(props.mode)
const form = ref<HTMLFormElement | null>(null)

async function handleSubmit() {
  if (await submit()) {
    await router.push({ name: 'dashboard' })
    return
  }
  await nextTick()
  form.value?.querySelector<HTMLInputElement>('[aria-invalid="true"]')?.focus()
}
</script>

<template>
  <div>
    <div class="welcome-mark"><AuthIcon :name="mode === 'login' ? 'leaf' : 'user'" /></div>
    <h1 id="auth-title">{{ mode === 'login' ? 'Boas-vindas de volta.' : 'Crie sua conta.' }}</h1>
    <p class="form-description">
      {{ mode === 'login'
        ? 'Seu MEI organizado começa por aqui. Entre na sua conta para continuar.'
        : 'Dê o primeiro passo para uma rotina mais organizada. Vamos começar?' }}
    </p>

    <form ref="form" novalidate @submit.prevent="handleSubmit">
      <div class="fields">
        <AuthField
          v-if="mode === 'register'"
          id="name"
          v-model="values.name"
          label="Seu nome"
          autocomplete="name"
          placeholder="Como podemos chamar você?"
          icon="user"
          :maxlength="255"
          :error="errors.name"
          @blur="touch('name')"
        />
        <AuthField
          id="email"
          v-model="values.email"
          label="E-mail"
          type="email"
          autocomplete="email"
          placeholder="voce@exemplo.com"
          icon="email"
          :maxlength="255"
          :error="errors.email"
          @blur="touch('email')"
        />
        <AuthField
          id="password"
          v-model="values.password"
          label="Senha"
          type="password"
          :autocomplete="mode === 'login' ? 'current-password' : 'new-password'"
          :placeholder="mode === 'login' ? 'Digite sua senha' : 'Crie uma senha'"
          icon="lock"
          :error="errors.password"
          @blur="touch('password')"
        />
        <AuthField
          v-if="mode === 'register'"
          id="password_confirmation"
          v-model="values.password_confirmation"
          label="Confirme sua senha"
          type="password"
          autocomplete="new-password"
          placeholder="Digite a senha novamente"
          icon="lock"
          :error="errors.password_confirmation"
          @blur="touch('password_confirmation')"
        />
      </div>

      <button class="submit-button" type="submit" :disabled="submitting">
        {{ submitting ? 'Aguarde...' : mode === 'login' ? 'Entrar na minha conta' : 'Criar minha conta' }}
      </button>
      <p v-if="notice" class="form-notice" role="status">{{ notice }}</p>
    </form>

    <div v-if="mode === 'login'" class="welcome-note">
      <span class="note-icon"><AuthIcon name="check" /></span>
      <p>Suas notas, seu faturamento.<br><strong>Tudo no mesmo lugar.</strong></p>
    </div>
  </div>
</template>

<style scoped>
.welcome-mark {
  display: grid;
  width: 46px;
  height: 46px;
  place-items: center;
  margin-bottom: 22px;
  border: 1px solid #e1e9da;
  border-radius: 14px;
  color: var(--color-forest);
  background: #f3f6ed;
}
.welcome-mark svg {
  width: 23px;
  height: 23px;
}
h1 {
  margin: 0;
  font-size: 30px;
  font-weight: 800;
  line-height: 1.25;
  letter-spacing: -1.2px;
}
.form-description {
  margin: 12px 0 29px;
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.8;
}
.fields {
  display: grid;
  gap: 19px;
}
.submit-button {
  width: 100%;
  min-height: 51px;
  margin-top: 27px;
  padding: 13px 20px;
  border: 1px solid var(--color-forest);
  border-radius: 9px;
  color: var(--color-paper);
  background: var(--color-forest);
  font-size: 13px;
  font-weight: 700;
}
.submit-button:hover {
  border-color: var(--color-forest-hover);
  background: var(--color-forest-hover);
}
.submit-button:active {
  background: #12321f;
}
.preview-note {
  margin: 12px 0 0;
  color: var(--color-muted);
  font-size: 10px;
  text-align: center;
}
.form-notice {
  margin: 18px 0 0;
  padding: 12px 14px;
  border-left: 3px solid var(--color-forest);
  border-radius: 0 6px 6px 0;
  background: var(--color-sage);
  font-size: 12px;
}
.welcome-note {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 32px;
  padding-top: 25px;
  border-top: 1px solid #ecede8;
}
.note-icon {
  display: grid;
  width: 30px;
  height: 30px;
  place-items: center;
  border-radius: 50%;
  color: #5b744b;
  background: #f1f5eb;
}
.note-icon svg {
  width: 16px;
  height: 16px;
}
.welcome-note p {
  margin: 0;
  color: var(--color-muted);
  font-size: 11px;
  line-height: 1.8;
}
.welcome-note strong {
  color: #536149;
  font-weight: 600;
}
@media (max-width: 760px) {
  h1 {
    font-size: 27px;
  }
  .welcome-mark {
    display: none;
  }
  .form-description {
    margin-bottom: 24px;
  }
  .welcome-note {
    margin-top: 25px;
    padding-top: 20px;
  }
}
</style>
