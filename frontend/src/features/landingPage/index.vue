<script setup lang="ts">
import LandingHeader from './components/LandingHeader.vue'
import LandingHero from './components/LandingHero.vue'
import LandingFeatures from './components/LandingFeatures.vue'
import LandingSteps from './components/LandingSteps.vue'
import LandingFaq from './components/LandingFaq.vue'
import LandingClosing from './components/LandingClosing.vue'

defineOptions({ name: 'LandingPageView' })
</script>

<template>
  <div class="landing-page">
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
    <div class="page-container">
      <LandingHeader />
      <main id="conteudo" tabindex="-1">
        <LandingHero />
        <LandingFeatures />
        <LandingSteps />
        <LandingFaq />
      </main>
      <LandingClosing />
    </div>
  </div>
</template>

<style scoped>
.landing-page {
  min-height: 100svh;
  background: #fbfcf9;
}
.page-container {
  width: min(1180px, calc(100% - 64px));
  margin-inline: auto;
}
.skip-link {
  position: fixed;
  z-index: 10;
  top: 12px;
  left: 16px;
  padding: 12px 20px;
  border-radius: 8px;
  background: var(--color-paper);
  transform: translateY(-200%);
}
.skip-link:focus {
  transform: translateY(0);
}
@media (max-width: 760px) {
  .page-container {
    width: calc(100% - 32px);
  }
}
@media (max-width: 360px) {
  .page-container {
    width: calc(100% - 24px);
  }
}
</style>
