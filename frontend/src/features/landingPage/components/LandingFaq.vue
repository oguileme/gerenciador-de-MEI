<script setup lang="ts">
const questions = [
  {
    question: 'O Meu MEI já está disponível para uso?',
    answer: 'Estamos construindo o Meu MEI. Por enquanto, você pode conhecer as telas de entrada e cadastro. A criação de contas e a gestão do negócio ainda não estão disponíveis nesta prévia.',
  },
  {
    question: 'O sistema vai emitir notas fiscais?',
    answer: 'Não. A proposta é organizar e acompanhar notas já emitidas. A emissão continua sendo feita no portal emissor que você utiliza.',
  },
  {
    question: 'Que tipo de nota poderei organizar?',
    answer: 'O foco inicial são as notas fiscais de serviço eletrônicas (NFS-e), a partir dos arquivos XML. O XML original faz parte da organização, junto das informações da nota.',
  },
  {
    question: 'Poderei cuidar de mais de um MEI?',
    answer: 'Sim, essa possibilidade faz parte da proposta. Uma mesma conta poderá reunir vários MEIs, mantendo as notas, os orçamentos e o acompanhamento de cada negócio separados.',
  },
]
</script>

<template>
  <section id="duvidas" class="faq-section" aria-labelledby="faq-title">
    <div class="faq-intro">
      <h2 id="faq-title">Antes de começar,<br>vamos esclarecer?</h2>
      <p>Um pouco mais sobre o Meu MEI<br>e o que estamos preparando.</p>
    </div>
    <div class="questions">
      <details v-for="item in questions" :key="item.question">
        <summary>{{ item.question }}<span aria-hidden="true" class="toggle-mark" /></summary>
        <p>{{ item.answer }}</p>
      </details>
    </div>
  </section>
</template>

<style scoped>
.faq-section {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: 70px;
  padding: 94px 28px;
  scroll-margin-top: 28px;
}
h2 {
  margin: 0;
  color: var(--color-forest);
  font-size: 33px;
  font-weight: 700;
  line-height: 1.3;
  letter-spacing: -1px;
}
.faq-intro p {
  margin: 19px 0 0;
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.9;
}
details {
  border-bottom: 1px solid var(--color-border);
}
details:first-child {
  border-top: 1px solid var(--color-border);
}
summary {
  display: flex;
  min-height: 70px;
  align-items: center;
  justify-content: space-between;
  gap: 22px;
  padding-block: 20px;
  font-size: 13px;
  font-weight: 700;
  list-style: none;
  cursor: pointer;
}
summary::-webkit-details-marker {
  display: none;
}
summary:hover {
  color: var(--color-forest);
}
.toggle-mark {
  position: relative;
  width: 16px;
  height: 16px;
  flex: 0 0 auto;
  color: var(--color-muted);
}
.toggle-mark::before,
.toggle-mark::after {
  position: absolute;
  top: 7px;
  left: 2px;
  width: 12px;
  height: 1.5px;
  border-radius: 2px;
  background: currentColor;
  content: '';
}
.toggle-mark::after {
  transform: rotate(90deg);
}
details[open] .toggle-mark::after {
  transform: rotate(0);
}
details p {
  max-width: 490px;
  margin: 0 30px 23px 0;
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.9;
}
@media (max-width: 900px) {
  .faq-section {
    gap: 35px;
  }
}
@media (max-width: 760px) {
  .faq-section {
    grid-template-columns: 1fr;
    padding: 56px 12px;
  }
  h2 {
    font-size: 30px;
  }
  summary {
    font-size: 14px;
  }
}
</style>
