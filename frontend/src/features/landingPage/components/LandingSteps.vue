<script setup lang="ts">
const steps = [
  {
    title: 'Comece pelo seu MEI',
    description: 'Reúna os dados do seu negócio. Cuida de mais de um MEI? A proposta é manter cada um no seu espaço.',
  },
  {
    title: 'Traga suas notas fiscais',
    description: 'Use os arquivos XML das suas NFS-e como ponto de partida para organizar os serviços prestados.',
  },
  {
    title: 'Veja o negócio por inteiro',
    description: 'Consulte suas notas, acompanhe o faturamento e mantenha seus orçamentos por perto.',
  },
]
</script>

<template>
  <section id="como-funciona" class="steps-section" aria-labelledby="steps-title">
    <div class="steps-intro">
      <span class="leaf-mark" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 37C1 20 17 7 39 7c0 21-10 37-29 30Z M8 42 30 19" />
        </svg>
      </span>
      <h2 id="steps-title">Um passo de cada vez.<br>Uma rotina mais simples.</h2>
      <p>
        Você já cuida de muita coisa. O Meu MEI está sendo pensado para tornar
        a organização do seu negócio uma parte mais leve do dia.
      </p>
    </div>
    <ol class="steps-list">
      <li v-for="(step, index) in steps" :key="step.title">
        <span class="step-number" aria-hidden="true">{{ index + 1 }}</span>
        <div>
          <h3>{{ step.title }}</h3>
          <p>{{ step.description }}</p>
        </div>
      </li>
    </ol>
  </section>
</template>

<style scoped>
.steps-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  align-items: center;
  gap: 70px;
  padding: 58px 54px;
  border: 1px solid #e1e8db;
  border-radius: 24px;
  background: #f1f5ed;
  scroll-margin-top: 28px;
}
.leaf-mark {
  display: block;
  width: 43px;
  height: 43px;
  margin-bottom: 22px;
  color: #789466;
}
.leaf-mark svg {
  width: 100%;
  height: 100%;
}
h2 {
  margin: 0;
  color: var(--color-forest);
  font-size: 31px;
  font-weight: 700;
  line-height: 1.3;
  letter-spacing: -1px;
}
.steps-intro p {
  max-width: 360px;
  margin: 20px 0 0;
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.9;
}
.steps-list {
  display: grid;
  gap: 30px;
  margin: 0;
  padding: 0;
  list-style: none;
}
li {
  display: flex;
  align-items: flex-start;
  gap: 18px;
}
.step-number {
  display: grid;
  width: 33px;
  height: 33px;
  flex: 0 0 auto;
  place-items: center;
  border: 1px solid #b7c9ac;
  border-radius: 50%;
  color: var(--color-forest);
  font-size: 12px;
  font-weight: 700;
}
h3 {
  margin: 2px 0 8px;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.3px;
}
li p {
  margin: 0;
  color: var(--color-muted);
  font-size: 12px;
  line-height: 1.9;
}
@media (max-width: 1000px) {
  .steps-section {
    gap: 36px;
    padding: 40px 32px;
  }
}
@media (max-width: 760px) {
  .steps-section {
    grid-template-columns: 1fr;
    gap: 35px;
    padding: 30px 26px;
    border-radius: 20px;
  }
  h2 {
    font-size: 28px;
  }
  li {
    gap: 13px;
  }
  li p {
    font-size: 13px;
  }
}
</style>
