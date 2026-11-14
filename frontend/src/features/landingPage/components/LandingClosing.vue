<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppBrand from '@/components/AppBrand.vue'
</script>

<template>
  <section class="closing" aria-labelledby="closing-title">
    <div>
      <h2 id="closing-title">Seu próximo passo<br>pode ser mais leve.</h2>
      <p>Conheça o começo de uma nova rotina para o seu MEI.</p>
    </div>
    <div class="closing-actions">
      <RouterLink class="signup-link" to="/register">Criar minha conta</RouterLink>
      <p>Já conhece a prévia? <RouterLink to="/login">Entrar</RouterLink></p>
    </div>
  </section>
  <footer class="site-footer">
    <div class="footer-brand">
      <AppBrand />
      <p>Feito para quem faz acontecer.</p>
    </div>
    <nav aria-label="Navegação do rodapé">
      <a href="#recursos">Recursos</a>
      <a href="#como-funciona">Como funciona</a>
      <a href="#duvidas">Dúvidas</a>
    </nav>
    <p class="footer-note">Uma ideia em construção.<br>Um cuidado com o seu negócio.</p>
  </footer>
</template>

<style scoped>
.closing {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 40px;
  padding: 48px 60px;
  border-radius: 24px;
  color: var(--color-on-accent);
  background: var(--color-forest);
}
h2 {
  margin: 0;
  font-size: 36px;
  font-weight: 600;
  line-height: 1.25;
  letter-spacing: -1px;
}
.closing > div > p {
  margin: 15px 0 0;
  color: color-mix(in srgb, var(--color-on-accent) 78%, var(--color-forest));
  font-size: 13px;
  line-height: 1.8;
}
.closing-actions {
  flex: 0 0 auto;
  text-align: center;
}
.signup-link {
  display: inline-flex;
  min-width: 205px;
  min-height: 50px;
  align-items: center;
  justify-content: center;
  padding: 14px 25px;
  border-radius: 8px;
  color: var(--color-forest);
  background: var(--color-sage);
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
}
.signup-link:hover {
  background: var(--color-surface-raised);
}
.closing-actions p a {
  display: inline-flex;
  min-height: 32px;
  align-items: center;
  margin-left: 4px;
  color: var(--color-on-accent);
  font-weight: 700;
}
.closing a:focus-visible {
  outline-color: var(--color-leaf);
}
.site-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  padding: 50px 4px 38px;
}
.footer-brand p {
  margin: 12px 0 0;
  color: var(--color-muted);
  font-size: 11px;
}
nav {
  display: flex;
  gap: 26px;
}
nav a {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  color: var(--color-muted);
  font-size: 12px;
  text-decoration: none;
}
nav a:hover {
  text-decoration: underline;
}
.footer-note {
  margin: 0;
  color: var(--color-muted);
  font-size: 11px;
  line-height: 1.9;
  text-align: right;
}
@media (max-width: 900px) {
  .closing {
    padding: 40px 32px;
  }
  .site-footer {
    flex-wrap: wrap;
  }
  .footer-note {
    width: 100%;
    text-align: left;
  }
}
@media (max-width: 760px) {
  .closing {
    align-items: flex-start;
    flex-direction: column;
    gap: 27px;
    padding: 32px 26px;
    border-radius: 20px;
  }
  h2 {
    font-size: 30px;
  }
  .closing-actions {
    width: 100%;
  }
  .signup-link {
    width: 100%;
  }
  .site-footer {
    gap: 20px;
    padding: 34px 12px 28px;
  }
  nav {
    flex-wrap: wrap;
    gap: 20px;
  }
}
</style>
