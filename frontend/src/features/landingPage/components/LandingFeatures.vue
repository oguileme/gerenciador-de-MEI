<script setup lang="ts">
const features = [
  {
    title: 'Suas notas, organizadas',
    description: 'Reúna os XMLs das suas NFS-e e encontre as informações de cada serviço prestado.',
    icon: 'M7 3h7l4 4v14H7z M14 3v5h4 M10 12h5 M10 16h5 M4 7v14',
  },
  {
    title: 'Faturamento à vista',
    description: 'Acompanhe o faturamento do seu MEI e a relação com o teto anual, com mais clareza.',
    icon: 'M4 3v17h17 M8 15v-4 M13 15V7 M18 15V4',
  },
  {
    title: 'Orçamentos no lugar',
    description: 'Organize suas propostas de serviço e consulte seus orçamentos no mesmo espaço.',
    icon: 'M4 6h6l2 3h8v11H4z M4 6V4h6l2 2h8v3 M8 13h8 M8 16h5',
  },
]
</script>

<template>
  <section id="recursos" class="features" aria-labelledby="features-title">
    <div class="section-heading">
      <h2 id="features-title">Cada coisa<br>no seu lugar.</h2>
      <p>
        Estamos construindo um espaço para reunir o que você precisa acompanhar,
        sem complicar a sua rotina.
      </p>
    </div>
    <div class="feature-list">
      <article v-for="feature in features" :key="feature.title" class="feature">
        <span class="feature-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path :d="feature.icon" />
          </svg>
        </span>
        <h3>{{ feature.title }}</h3>
        <p>{{ feature.description }}</p>
      </article>
    </div>
  </section>
</template>

<style scoped>
.features {
  padding: 94px 28px 88px;
  scroll-margin-top: 28px;
}
.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 48px;
  margin-bottom: 46px;
}
h2 {
  margin: 0;
  color: var(--color-forest);
  font-size: 37px;
  font-weight: 700;
  line-height: 1.25;
  letter-spacing: -1.3px;
}
.section-heading p {
  max-width: 380px;
  margin: 0;
  color: var(--color-muted);
  font-size: 14px;
  line-height: 1.9;
}
.feature-list {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}
.feature {
  padding: 0 36px;
  border-left: 1px solid var(--color-border);
}
.feature:first-child {
  padding-left: 0;
  border-left: 0;
}
.feature:last-child {
  padding-right: 0;
}
.feature-icon {
  display: grid;
  width: 47px;
  height: 47px;
  place-items: center;
  border-radius: 13px;
  color: var(--color-forest);
  background: #edf2e8;
}
.feature-icon svg {
  width: 24px;
  height: 24px;
}
h3 {
  margin: 21px 0 10px;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.4px;
}
.feature p {
  max-width: 290px;
  margin: 0;
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.9;
}
@media (max-width: 760px) {
  .features {
    padding: 56px 12px;
  }
  .section-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 18px;
    margin-bottom: 32px;
  }
  h2 {
    font-size: 31px;
  }
  .feature-list {
    grid-template-columns: 1fr;
    gap: 26px;
  }
  .feature,
  .feature:first-child,
  .feature:last-child {
    padding: 0 0 26px;
    border: 0;
    border-bottom: 1px solid var(--color-border);
  }
  .feature:last-child {
    padding-bottom: 0;
    border: 0;
  }
  .feature p {
    max-width: 460px;
    font-size: 14px;
  }
  h3 {
    margin-top: 15px;
  }
}
</style>
