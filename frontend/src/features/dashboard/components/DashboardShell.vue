<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import AppBrand from '@/components/AppBrand.vue'
import type { Mei } from '@/types/mei'
import { formatMoney } from '@/utils/format'
import { useDashboard } from '../composables/useDashboard'
import RevenueOverview from './RevenueOverview.vue'
import MonthlyChart from './MonthlyChart.vue'
import InvoiceList from './InvoiceList.vue'
import DashboardSettings from './DashboardSettings.vue'

const props = defineProps<{ mei: Mei }>()
const emit = defineEmits<{ updated: [mei: Mei] }>()
const { metrics, year, selectedMonth, loading, error, load } = useDashboard(String(props.mei.id))
const invoiceList = ref<InstanceType<typeof InvoiceList> | null>(null)
const settings = ref<'mei' | 'limit' | null>(null)
const opener = ref<HTMLElement | null>(null)
const notice = ref('')
const selectedSection = ref('overview')
const years = computed(() => {
  const current = metrics.value ? Number(metrics.value.as_of.slice(0, 4)) : year.value
  if (!current) return []
  const opening = props.mei.opening_date ? Number(props.mei.opening_date.slice(0, 4)) : current
  const first = Math.min(current, Math.max(1900, opening))
  return Array.from({ length: current - first + 1 }, (_, index) => current - index)
})
onMounted(load)
function openSettings(mode: 'mei' | 'limit') {
  opener.value = document.activeElement instanceof HTMLElement ? document.activeElement : null
  settings.value = mode
}
async function closeSettings() {
  settings.value = null
  await nextTick()
  opener.value?.focus()
}
async function saved(mei?: Mei) {
  if (mei) emit('updated', mei)
  await closeSettings()
  notice.value = 'Alterações salvas.'
  await load()
}
</script>

<template>
  <div class="dashboard-shell">
    <aside class="sidebar">
      <AppBrand />
      <div class="business"><span class="business-icon" aria-hidden="true">{{ mei.mei_name.slice(0, 1).toLocaleUpperCase('pt-BR') }}</span><div><strong>{{ mei.mei_name }}</strong><span>Seu espaço de trabalho</span></div></div>
      <nav aria-label="Navegação do MEI">
        <a href="#overview" :class="{ current: selectedSection === 'overview' }" @click="selectedSection = 'overview'">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" /></svg>Visão geral
        </a>
        <a href="#invoices" :class="{ current: selectedSection === 'invoices' }" @click="selectedSection = 'invoices'">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3h9l3 3v15H6zM9 10h6M9 14h6M9 18h3" /></svg>Notas fiscais
        </a>
        <button @click="openSettings('mei')">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 21V7l8-4 8 4v14M2 21h20M9 21v-6h6v6M8 8h1m6 0h1M8 11h1m6 0h1" /></svg>Dados do MEI
        </button>
      </nav>
      <div class="sidebar-bottom"><RouterLink :to="{ name: 'mei-selection' }">Trocar MEI</RouterLink><p>Um negócio de cada vez.<br>Tudo no seu lugar.</p></div>
    </aside>
    <main id="overview">
      <header class="dashboard-heading">
        <div><p class="business-mobile">{{ mei.mei_name }}</p><h1>Visão geral</h1><p>Um olhar sobre o seu negócio.</p></div>
        <div class="header-actions">
          <label v-if="year" class="year-filter">Ano <select v-model="year" :disabled="loading" @change="load"><option v-for="option in years.length ? years : [year]" :key="option" :value="option">{{ option }}</option></select></label>
          <button class="button-primary" @click="invoiceList?.openImport()"><span aria-hidden="true">+</span> Importar XML</button>
        </div>
      </header>
      <p v-if="notice" class="saved-notice" role="status">{{ notice }}</p>
      <div v-if="loading" class="loading-panel" role="status">Atualizando seu faturamento…</div>
      <div v-else-if="error" class="loading-panel" role="alert"><p>{{ error }}</p><button class="button-secondary" @click="load">Tentar novamente</button></div>
      <template v-else-if="metrics">
        <RevenueOverview :metrics="metrics" :month="selectedMonth" @edit-limit="openSettings('limit')" />
        <div class="chart-row">
          <MonthlyChart :months="metrics.months" :average="metrics.monthly_average" :year="metrics.year" :selected-month="selectedMonth" @select="selectedMonth = $event" />
          <section class="ceiling-panel" aria-labelledby="ceiling-heading">
            <h2 id="ceiling-heading">De olho no limite</h2>
            <p class="ceiling-description">Limite de acompanhamento em {{ metrics.year }}</p>
            <p class="ceiling-amount">{{ formatMoney(metrics.ceiling.amount) }}</p>
            <div class="remaining"><span>Disponível até o limite</span><strong>{{ formatMoney(metrics.ceiling.remaining_amount) }}</strong></div>
            <div class="thresholds"><p><span class="dot safe"></span>Seguro: abaixo de {{ metrics.ceiling.warning_percent }}%</p><p><span class="dot warning"></span>Atenção: a partir de {{ metrics.ceiling.warning_percent }}%</p><p><span class="dot danger"></span>Atenção máxima: a partir de {{ metrics.ceiling.danger_percent }}%</p></div>
            <button class="button-secondary" @click="openSettings('limit')">Alterar limite anual</button>
          </section>
        </div>
      </template>
      <InvoiceList ref="invoiceList" :mei-id="String(mei.id)" :refresh-metrics="load" />
      <footer>Meu MEI. Seu negócio em dia.</footer>
    </main>
    <DashboardSettings v-if="settings && (settings === 'mei' || metrics)" :mei="mei" :mode="settings" :year="year ?? 0" :amount="metrics?.ceiling.amount ?? '0.00'" @close="closeSettings" @saved="saved" />
  </div>
</template>

<style scoped>
.dashboard-shell {
  display: grid;
  grid-template-columns: 224px minmax(0, 1fr);
  min-height: 100svh;
  background: #f3f5f0;
}

.sidebar {
  display: flex;
  flex-direction: column;
  padding: 32px 22px;
  background: white;
  border-right: 1px solid var(--color-border);
  position: sticky;
  top: 0;
  height: 100svh;
}

.business {
  display: flex;
  gap: 10px;
  align-items: center;
  margin: 46px 0 32px;
}

.business-icon {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  background: var(--color-sage);
  border-radius: 10px;
  font-weight: 800;
}

.business strong {
  display: block;
  font-size: 12px;
  overflow-wrap: anywhere;
}

.business span:not(.business-icon) {
  display: block;
  color: var(--color-muted);
  font-size: 10px;
  margin-top: 2px;
}

nav {
  display: grid;
  gap: 8px;
}

nav a, nav button {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--color-muted);
  text-decoration: none;
  background: none;
  border: 0;
  padding: 13px 14px;
  border-radius: 10px;
  font-size: 13px;
  text-align: left;
}

nav .current {
  color: var(--color-forest);
  background: var(--color-sage);
  font-weight: 700;
}

nav a:hover, nav button:hover {
  background: #f3f5f0;
  color: var(--color-forest);
}

nav svg {
  width: 18px;
  height: 18px;
  stroke: currentColor;
  stroke-width: 1.6;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.sidebar-bottom {
  margin-top: auto;
  padding: 24px 10px 0;
}

.sidebar-bottom a {
  font-size: 13px;
  font-weight: 700;
}

.sidebar-bottom p {
  color: var(--color-muted);
  font-size: 11px;
  margin: 18px 0 0;
  line-height: 1.8;
}

main {
  width: min(1380px, 100%);
  margin-inline: auto;
  padding: 36px 36px 20px;
  min-width: 0;
}

.dashboard-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 32px;
}

h1 {
  font-size: 30px;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -1px;
  margin: 0;
}

.dashboard-heading p {
  font-size: 12px;
  color: var(--color-muted);
  margin: 10px 0 0;
}

.business-mobile {
  display: none;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.year-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--color-muted);
  font-size: 12px;
}

.year-filter select {
  font: inherit;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  background: white;
  color: var(--color-text);
  padding: 10px;
}

.dashboard-shell :deep(.button-primary), .dashboard-shell :deep(.button-secondary) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  min-height: 42px;
  border-radius: 9px;
  padding: 10px 16px;
  font-size: 12px;
  font-weight: 700;
  border: 1px solid var(--color-border);
}

.dashboard-shell :deep(.button-primary) {
  background: var(--color-forest);
  color: white;
  border-color: var(--color-forest);
}

.dashboard-shell :deep(.button-primary:hover) {
  background: var(--color-forest-hover);
}

.dashboard-shell :deep(.button-secondary) {
  background: white;
  color: var(--color-forest);
}

.dashboard-shell :deep(button:disabled) {
  opacity: .55;
  cursor: wait;
}

.chart-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: 20px;
  margin-bottom: 26px;
}

.ceiling-panel {
  padding: 26px;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  background: #eaf1e7;
}

.ceiling-panel h2 {
  font-size: 18px;
  margin: 0;
  letter-spacing: -.4px;
}

.ceiling-description {
  color: #52634f;
  font-size: 11px;
  margin: 8px 0 16px;
}

.ceiling-amount {
  font-size: 26px;
  font-weight: 700;
  letter-spacing: -.8px;
  margin: 0 0 20px;
  overflow-wrap: anywhere;
}

.remaining {
  display: grid;
  gap: 4px;
  border-top: 1px solid #cfddc9;
  padding-top: 17px;
}

.remaining span {
  font-size: 11px;
  color: #52634f;
}

.remaining strong {
  font-size: 18px;
}

.thresholds {
  margin: 20px 0;
  font-size: 10px;
}

.thresholds p {
  display: flex;
  gap: 8px;
  align-items: center;
  margin: 7px 0;
}

.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.safe {
  background: var(--color-forest);
}

.warning {
  background: #93610c;
}

.danger {
  background: var(--color-error);
}

.loading-panel {
  padding: 60px 24px;
  text-align: center;
  background: white;
  border-radius: 20px;
  margin-bottom: 26px;
  color: var(--color-muted);
}

.saved-notice {
  color: var(--color-forest);
  font-size: 12px;
}

footer {
  color: var(--color-muted);
  font-size: 10px;
  text-align: center;
  padding: 24px 0 4px;
}

@media (max-width: 1200px) {
  .dashboard-shell {
    grid-template-columns: 196px minmax(0, 1fr);
  }
  .sidebar {
    padding-inline: 16px;
  }
  main {
    padding: 28px 24px;
  }
  .chart-row {
    grid-template-columns: minmax(0, 1fr);
  }
  .ceiling-panel {
    display: none;
  }
}

@media (max-width: 800px) {
  .dashboard-shell {
    display: block;
  }
  .sidebar {
    position: static;
    height: auto;
    padding: 18px 20px;
    border-right: 0;
    border-bottom: 1px solid var(--color-border);
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .business, .sidebar-bottom p {
    display: none;
  }
  .sidebar-bottom {
    margin: 0;
    padding: 0;
    order: 1;
  }
  nav {
    order: 2;
    display: flex;
    width: 100%;
    gap: 4px;
  }
  nav a, nav button {
    padding: 10px;
    font-size: 12px;
    gap: 7px;
  }
  .business-mobile {
    display: block;
    margin: 0 0 8px !important;
  }
  main {
    padding: 24px 20px;
  }
  .dashboard-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 20px;
  }
  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
}

@media (max-width: 400px) {
  main {
    padding-inline: 12px;
  }
  .sidebar {
    padding-inline: 12px;
  }
  nav a, nav button {
    font-size: 11px;
    padding: 9px;
  }
}
</style>
