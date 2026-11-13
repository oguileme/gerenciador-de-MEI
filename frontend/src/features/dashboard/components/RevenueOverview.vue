<script setup lang="ts">
import type { DashboardMetrics } from '@/types/dashboard'
import { formatMoney } from '@/utils/format'

defineProps<{ metrics: DashboardMetrics; month: number }>()
const emit = defineEmits<{ editLimit: [] }>()
const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
const states = { safe: 'Dentro da faixa segura', warning: 'Atenção ao limite', danger: 'Faixa de atenção máxima' }
</script>

<template>
  <div class="revenue-overview">
    <section class="annual-card" aria-labelledby="annual-heading">
      <h2 id="annual-heading">Faturamento bruto anual</h2>
      <p class="annual-amount">{{ formatMoney(metrics.annual_revenue) }}</p>
      <p class="annual-description">{{ metrics.active_invoice_count }} {{ metrics.active_invoice_count === 1 ? 'nota ativa' : 'notas ativas' }} em {{ metrics.year }}</p>
      <div class="limit-track" :class="metrics.ceiling.status" role="img" :aria-label="`${metrics.ceiling.used_percent.replace('.', ',')}% do limite utilizado`"><span :style="{ width: `min(100%, ${metrics.ceiling.used_percent}%)` }"></span></div>
      <div class="limit-caption"><span>{{ metrics.ceiling.used_percent.replace('.', ',') }}% do limite</span><button @click="emit('editLimit')">{{ formatMoney(metrics.ceiling.amount) }} <span class="sr-only">Alterar limite anual</span></button></div>
    </section>
    <section class="metric-card" aria-labelledby="monthly-heading">
      <h2 id="monthly-heading">{{ months[month - 1] }} de {{ metrics.year }}</h2>
      <p class="metric-amount">{{ formatMoney(metrics.months.find((entry) => entry.month === month)?.amount ?? '0.00') }}</p>
      <p>Faturamento bruto do mês</p>
      <span class="month-note">Consulte os outros meses no gráfico.</span>
    </section>
    <section class="metric-card" aria-labelledby="projection-heading">
      <h2 id="projection-heading">{{ metrics.projection.status === 'closed_year' ? 'Ano encerrado' : 'Projeção até dezembro' }}</h2>
      <p class="metric-amount">{{ metrics.projection.amount === null ? '—' : formatMoney(metrics.projection.amount) }}</p>
      <p v-if="metrics.projection.status === 'insufficient_data'">Aguardando o primeiro mês completo de atividade.</p>
      <template v-else-if="metrics.projection.status !== 'closed_year'">
        <p :class="{ 'projection-warning': metrics.projection.status === 'over_limit' }">{{ metrics.projection.status === 'over_limit' ? 'Neste ritmo, a estimativa supera o limite.' : 'Neste ritmo, a estimativa fica dentro do limite.' }}</p>
        <span class="month-note">Base: {{ metrics.projection.completed_months }} meses completos. Uma estimativa, não uma garantia.</span>
      </template>
      <p v-else>Valor realizado no período.</p>
    </section>
  </div>
  <p class="scope-note">Considera as notas ativas registradas, pela data de emissão. <span :class="['state', metrics.ceiling.status]">{{ states[metrics.ceiling.status] }}</span></p>
</template>

<style scoped>
.revenue-overview {
  display: grid;
  grid-template-columns: 1.25fr 1fr 1fr;
  gap: 18px;
}

.annual-card, .metric-card {
  padding: 25px;
  border-radius: 20px;
}

.annual-card {
  color: white;
  background: var(--color-forest);
}

h2 {
  margin: 0;
  font-size: 13px;
  font-weight: 500;
}

.annual-amount {
  margin: 20px 0 4px;
  font-size: clamp(25px, 2.8vw, 38px);
  letter-spacing: -1px;
  line-height: 1.2;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  overflow-wrap: anywhere;
}

.annual-description {
  font-size: 11px;
  color: #d8e6d4;
  margin: 8px 0 24px;
}

.limit-track {
  height: 5px;
  border-radius: 4px;
  background: #ffffff30;
  overflow: hidden;
}

.limit-track span {
  display: block;
  height: 100%;
  background: #c5e2ad;
}

.limit-track.warning span {
  background: #f4cb6f;
}

.limit-track.danger span {
  background: #ffa69a;
}

.limit-caption {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  font-size: 10px;
}

.limit-caption button {
  color: white;
  background: none;
  border: 0;
  padding: 6px 0;
  text-decoration: underline;
  font-size: 11px;
}

.metric-card {
  border: 1px solid var(--color-border);
  background: white;
}

.metric-card h2 {
  color: var(--color-muted);
}

.metric-amount {
  font-size: clamp(24px, 2.4vw, 32px);
  letter-spacing: -.8px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  line-height: 1.2;
  margin: 24px 0 12px;
  overflow-wrap: anywhere;
}

.metric-card > p:not(.metric-amount) {
  font-size: 12px;
  margin: 0;
}

.month-note {
  display: block;
  margin-top: 18px;
  font-size: 11px;
  color: var(--color-muted);
}

.projection-warning {
  color: #93610c;
}

.scope-note {
  margin: 14px 2px 24px;
  font-size: 11px;
  color: var(--color-muted);
  display: flex;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
}

.state {
  font-weight: 700;
}

.state.safe {
  color: var(--color-forest);
}

.state.warning {
  color: #93610c;
}

.state.danger {
  color: var(--color-error);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip-path: inset(50%);
}

@media (max-width: 1100px) {
  .revenue-overview {
    grid-template-columns: 1fr 1fr;
  }
  .annual-card {
    grid-column: 1 / -1;
  }
}

@media (max-width: 560px) {
  .revenue-overview {
    grid-template-columns: 1fr;
  }
  .annual-card, .metric-card {
    padding: 22px;
  }
  .annual-amount {
    font-size: 34px;
  }
}
</style>
