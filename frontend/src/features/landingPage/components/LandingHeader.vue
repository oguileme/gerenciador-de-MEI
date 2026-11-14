<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppBrand from '@/components/AppBrand.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
</script>

<template>
  <header class="site-header">
    <AppBrand />
    <nav class="section-links" aria-label="Seções da página">
      <a href="#recursos">Recursos</a>
      <a href="#como-funciona">Como funciona</a>
      <a href="#duvidas">Dúvidas</a>
    </nav>
    <nav class="account-links" aria-label="Acesso à conta">
      <ThemeToggle />
      <RouterLink class="login-link" to="/login">Entrar</RouterLink>
      <RouterLink class="signup-link" to="/register">Criar conta</RouterLink>
    </nav>
  </header>
</template>

<style scoped>
.site-header {
  display: flex;
  min-height: 108px;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
}
.site-header :deep(.brand) {
  align-self: center;
}
nav {
  display: flex;
  align-items: center;
}
.section-links {
  gap: 32px;
}
nav a {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}
.section-links a {
  color: var(--color-muted);
}
.section-links a:hover,
.login-link:hover {
  color: var(--color-forest);
  text-decoration: underline;
}
.account-links {
  gap: 18px;
}
.signup-link {
  justify-content: center;
  padding: 10px 23px;
  border-radius: 8px;
  color: var(--color-paper);
  background: var(--color-forest);
}
.signup-link:hover {
  background: var(--color-forest-hover);
}
@media (max-width: 900px) {
  .site-header {
    flex-wrap: wrap;
    gap: 8px 24px;
    padding-block: 20px 12px;
  }
  .section-links {
    order: 3;
    width: 100%;
    justify-content: center;
  }
}
@media (max-width: 480px) {
  .account-links {
    gap: 14px;
  }
  .signup-link {
    padding-inline: 14px;
  }
  .section-links {
    gap: 26px;
  }
  nav a {
    font-size: 12px;
  }
}
@media (max-width: 360px) {
  .site-header {
    gap: 8px;
  }
  .account-links {
    gap: 10px;
  }
  .signup-link {
    padding-inline: 10px;
  }
}
</style>
