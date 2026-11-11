<script setup lang="ts">
import { RouterLink } from 'vue-router'
import illustration from '@/assets/landing/mei-workspace.png'
</script>

<template>
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero-copy">
      <h1 id="hero-title">Seu negócio em dia.<br>Sua rotina mais leve.</h1>
      <p class="hero-description">
        Notas fiscais, faturamento e orçamentos em um só lugar.
        Mais clareza para cuidar do seu MEI e mais tempo para fazer o que você ama.
      </p>
      <div class="hero-actions">
        <RouterLink class="primary-action" to="/register">Criar minha conta</RouterLink>
        <a class="secondary-action" href="#como-funciona">Conhecer o Meu MEI</a>
      </div>
      <p class="preview-note">
        Estamos preparando o Meu MEI. Explore a prévia.
      </p>
    </div>
    <figure class="hero-art">
      <img
        :src="illustration"
        width="1448"
        height="1086"
        alt="Empreendedora conferindo uma nota à mesa, ao lado de seu notebook e uma pequena planta."
        fetchpriority="high"
      >
      <figcaption>Pequeno no nome. Grande nos planos.</figcaption>
    </figure>
  </section>
</template>

<style scoped>
.hero {
  display: grid;
  min-height: 570px;
  grid-template-columns: 1.05fr 1fr;
  align-items: center;
  gap: 12px;
  padding: 64px 52px 52px;
  border-radius: 26px;
  background: var(--color-sage);
}
.hero-copy {
  position: relative;
  z-index: 1;
}
h1 {
  margin: 0;
  color: var(--color-forest);
  font-size: clamp(36px, 3.8vw, 53px);
  font-weight: 700;
  line-height: 1.15;
  letter-spacing: -2.3px;
}
.hero-description {
  max-width: 410px;
  margin: 25px 0 30px;
  color: #596b5e;
  font-size: 15px;
  line-height: 1.9;
}
.hero-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}
.hero-actions a {
  display: inline-flex;
  min-height: 49px;
  align-items: center;
  justify-content: center;
  padding: 13px 21px;
  border: 1px solid var(--color-forest);
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
}
.primary-action {
  color: var(--color-paper);
  background: var(--color-forest);
}
.primary-action:hover {
  background: var(--color-forest-hover);
}
.secondary-action {
  background: transparent;
}
.secondary-action:hover {
  background: #ffffff70;
}
.preview-note {
  margin: 17px 0 0;
  color: #596b5e;
  font-size: 11px;
}
.hero-art {
  margin: 14px -20px 0 0;
}
.hero-art img {
  display: block;
  width: 100%;
  height: auto;
}
figcaption {
  margin-top: 17px;
  color: #52654e;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
}
@media (max-width: 1100px) {
  .hero {
    padding: 45px 32px;
  }
  h1 {
    font-size: clamp(31px, 3.8vw, 41px);
  }
}
@media (max-width: 760px) {
  .hero {
    min-height: 0;
    grid-template-columns: 1fr;
    gap: 28px;
    padding: 35px 26px 27px;
    border-radius: 20px;
  }
  h1 {
    font-size: clamp(31px, 6.7vw, 46px);
    letter-spacing: -1.5px;
  }
  .hero-description {
    max-width: 460px;
    margin-block: 20px 24px;
    font-size: 14px;
  }
  .hero-art {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
  }
  .preview-note {
    font-size: 10px;
  }
  figcaption {
    font-size: 11px;
  }
}
@media (max-width: 480px) {
  .hero-actions {
    align-items: stretch;
    flex-direction: column;
    gap: 10px;
  }
  .hero-actions a {
    font-size: 13px;
  }
}
</style>
