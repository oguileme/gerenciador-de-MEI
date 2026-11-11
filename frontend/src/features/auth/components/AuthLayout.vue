<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppBrand from '@/components/AppBrand.vue'
import illustration from '@/assets/auth/entrepreneur-cutout.png'
import type { AuthMode } from '../types/auth'
import AuthIcon from './AuthIcon.vue'

defineProps<{ mode: AuthMode }>()
</script>

<template>
  <main class="auth-page">
    <div class="auth-shell">
      <section class="story-panel" aria-labelledby="story-title">
        <AppBrand />

        <div class="story-heading">
          <h2 id="story-title">Seu negócio em dia.<br>Sua rotina mais leve.</h2>
          <p>Mais clareza para cuidar do seu MEI.<br>Mais tempo para fazer o que você ama.</p>
        </div>

        <img
          class="story-illustration"
          :src="illustration"
          alt="Empreendedora organizando suas notas e anotações em uma mesa com notebook e planta."
          width="1448"
          height="1086"
          fetchpriority="high"
        >

        <div class="story-caption">
          <p>Pequeno no nome. Grande nos planos.</p>
          <span>Seu próximo passo começa com organização.</span>
        </div>

        <ul class="story-benefits" aria-label="Recursos do Meu MEI">
          <li><AuthIcon name="check" /> Notas organizadas</li>
          <li><AuthIcon name="check" /> Faturamento à vista</li>
        </ul>
      </section>

      <section class="form-panel" aria-labelledby="auth-title">
        <p class="account-switch">
          {{ mode === 'login' ? 'Ainda não tem uma conta?' : 'Já tem uma conta?' }}
          <RouterLink :to="mode === 'login' ? '/register' : '/login'">
            {{ mode === 'login' ? 'Criar conta' : 'Entrar' }}
          </RouterLink>
        </p>
        <div class="form-content"><slot /></div>
        <p class="form-footer"><AuthIcon name="leaf" /> Um pouco de organização. Mais tranquilidade.</p>
      </section>
    </div>
    <footer class="page-footer">Meu MEI. Feito para quem faz acontecer.</footer>
  </main>
</template>

<style scoped>
.auth-page {
  display: flex;
  min-height: 100svh;
  flex-direction: column;
  justify-content: center;
  padding: 40px 32px 24px;
}

.auth-shell {
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: min(1180px, 100%);
  min-height: 750px;
  margin: auto;
  padding: 10px;
  border: 1px solid #e5e9e1;
  border-radius: 28px;
  background: var(--color-paper);
  box-shadow: 0 16px 64px #29473106;
}

.story-panel {
  display: flex;
  min-width: 0;
  flex-direction: column;
  padding: 34px 38px 30px;
  border-radius: 20px;
  background: var(--color-sage);
}

.story-heading {
  margin-top: 36px;
}

.story-heading h2 {
  margin: 0;
  color: var(--color-forest);
  font-size: clamp(30px, 3.1vw, 40px);
  font-weight: 700;
  line-height: 1.22;
  letter-spacing: -1.6px;
}

.story-heading p {
  margin: 17px 0 0;
  color: #596b5e;
  font-size: 14px;
  line-height: 1.8;
}

.story-illustration {
  display: block;
  width: calc(100% + 40px);
  height: 300px;
  margin: auto -20px;
  object-fit: contain;
}

.story-caption {
  margin-top: 9px;
  text-align: center;
}
.story-caption p {
  margin: 0 0 5px;
  font-size: 16px;
  font-weight: 700;
}
.story-caption span {
  color: #596b5e;
  font-size: 12px;
}

.story-benefits {
  display: flex;
  justify-content: center;
  gap: 22px;
  margin: 28px 0 0;
  padding: 21px 0 0;
  border-top: 1px solid #cedbc8;
  list-style: none;
}

.story-benefits li {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
}
.story-benefits svg {
  width: 15px;
  height: 15px;
}

.form-panel {
  display: flex;
  min-width: 0;
  flex-direction: column;
  padding: 32px 44px 25px;
}

.account-switch {
  margin: 5px 0 28px;
  color: var(--color-muted);
  font-size: 12px;
  text-align: right;
}

.account-switch a {
  margin-left: 7px;
  font-weight: 800;
  text-decoration: none;
}
.account-switch a:hover {
  text-decoration: underline;
}
.form-content {
  width: 100%;
  max-width: 370px;
  margin: auto;
  padding: 24px 0;
}
.form-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  margin: 25px 0 0;
  color: var(--color-muted);
  font-size: 10px;
}
.form-footer svg {
  width: 14px;
  height: 14px;
}
.page-footer {
  margin-top: 24px;
  color: #737d74;
  font-size: 11px;
  text-align: center;
}

@media (min-width: 1600px) {
  .auth-shell {
    min-height: 800px;
  }
}

@media (max-width: 1050px) {
  .auth-page {
    padding: 24px 20px;
  }
  .story-panel {
    padding-inline: 26px;
  }
  .form-panel {
    padding-inline: 28px;
  }
  .story-heading h2 {
    font-size: 32px;
  }
  .story-benefits {
    gap: 10px;
  }
}

@media (max-width: 760px) {
  .auth-page {
    justify-content: flex-start;
    padding: 16px;
  }
  .auth-shell {
    grid-template-columns: 1fr;
    min-height: 0;
    max-width: 500px;
    margin: 0 auto;
  }
  .story-panel {
    padding: 20px 22px;
    border-radius: 18px 18px 10px 10px;
  }
  .story-heading {
    margin-top: 17px;
  }
  .story-heading h2 {
    font-size: 23px;
    line-height: 1.3;
    letter-spacing: -0.7px;
  }
  .story-heading p, .story-illustration, .story-caption, .story-benefits {
    display: none;
  }
  .form-panel {
    padding: 22px;
  }
  .account-switch {
    margin: 0 0 8px;
    font-size: 11px;
    text-align: left;
  }
  .form-content {
    padding: 24px 0 8px;
  }
  .form-footer {
    margin-top: 24px;
    font-size: 9px;
  }
  .page-footer {
    margin-top: 18px;
    font-size: 10px;
  }
}

@media (max-width: 360px) {
  .auth-page {
    padding: 8px;
  }
  .form-panel {
    padding: 22px 15px;
  }
}
</style>
