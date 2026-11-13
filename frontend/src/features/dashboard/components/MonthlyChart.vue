<script setup lang="ts">
import type { DashboardMetrics } from '@/types/dashboard'
import { formatMoney } from '@/utils/format'

defineProps<{
  months: DashboardMetrics['months']
  average: DashboardMetrics['monthly_average']
  selectedMonth: number
  year: number
}>()
const emit = defineEmits<{ select: [month: number] }>()
const labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
</script>

<template>
  <section class="chart-panel" aria-labelledby="chart-heading">
    <div class="chart-heading"><div><h2 id="chart-heading">Seu ano, mês a mês</h2><p>Faturamento bruto por emissão</p></div><span>{{ year }}</span></div>
    <p v-if="average.amount !== null" class="average-label">
      <span class="average-swatch" aria-hidden="true"></span>
      Média mensal: <strong>{{ formatMoney(average.amount) }}</strong>
      <span class="average-period">({{ average.month_count }} {{ average.month_count === 1 ? 'mês' : 'meses' }})</span>
    </p>
    <p v-else class="average-unavailable">Média mensal indisponível neste período.</p>
    <div class="chart" role="group" :aria-label="`Faturamento mensal de ${year}`">
      <button v-for="entry in months" :key="entry.month" class="month" :class="{ selected: selectedMonth === entry.month }"
        :aria-pressed="selectedMonth === entry.month"
        :aria-label="`${labels[entry.month - 1]}: ${formatMoney(entry.amount)}`"
        @click="emit('select', entry.month)">
        <span class="bar-area"><span class="bar" :style="{ height: `max(3px, ${entry.height_percent}%)` }"></span><span class="tooltip">{{ formatMoney(entry.amount) }}</span></span>
        <span class="month-label">{{ labels[entry.month - 1] }}</span>
      </button>
      <div v-if="average.height_percent !== null" class="average-guide" aria-hidden="true">
        <span class="average-line" :style="{ bottom: `${average.height_percent}%` }"></span>
      </div>
    </div>
    <p class="chart-hint">Selecione um mês para consultar o valor.</p>
  </section>
</template>

<style scoped>
.chart-panel {
  min-width: 0;
  background: white;
  padding: 26px;
  border: 1px solid var(--color-border);
  border-radius: 20px;
}

.chart-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

h2 {
  margin: 0;
  font-size: 18px;
  letter-spacing: -.4px;
}

.chart-heading p, .chart-heading > span {
  color: var(--color-muted);
  font-size: 12px;
  margin: 6px 0 0;
}

.average-label, .average-unavailable {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 5px;
  margin: 18px 0 0;
  font-size: 12px;
  color: var(--color-forest);
}

.average-period, .average-unavailable {
  color: var(--color-muted);
  font-size: 11px;
}

.average-swatch {
  width: 22px;
  border-top: 2px dashed var(--color-forest);
  margin-right: 3px;
}

.average-guide {
  position: absolute;
  inset: 0 0 var(--month-label-height);
  pointer-events: none;
  z-index: 1;
}

.average-line {
  position: absolute;
  inset-inline: 0;
  border-top: 2px dashed var(--color-forest);
}

.chart {
  --plot-height: 184px;
  --month-label-height: 34px;
  position: relative;
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  height: calc(var(--plot-height) + var(--month-label-height));
  gap: 12px;
  margin-top: 26px;
  background: repeating-linear-gradient(to top, transparent 0, transparent 49px, #edf0e9 49px, #edf0e9 50px);
}

.month {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  min-width: 0;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--color-muted);
}

.bar-area {
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  height: var(--plot-height);
  width: 100%;
}

.bar {
  display: block;
  width: min(100%, 34px);
  border-radius: 7px 7px 2px 2px;
  background: #b8cfb0;
}

.selected .bar {
  background: var(--color-forest);
}

.month:hover .bar, .month:focus-visible .bar {
  background: #61814e;
}

.month-label {
  display: block;
  padding-top: 12px;
  height: var(--month-label-height);
  font-size: 11px;
}

.selected .month-label {
  color: var(--color-forest);
  font-weight: 800;
}

.tooltip {
  display: none;
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-forest);
  color: white;
  border-radius: 6px;
  padding: 5px 8px;
  font-size: 10px;
  white-space: nowrap;
  z-index: 2;
}

.month:hover .tooltip, .month:focus-visible .tooltip {
  display: block;
}

.month:first-child .tooltip {
  left: 0;
  transform: none;
}

.month:nth-of-type(12) .tooltip {
  left: auto;
  right: 0;
  transform: none;
}

.chart-hint {
  color: var(--color-muted);
  font-size: 11px;
  margin: 18px 0 0;
}

@media (max-width: 600px) {
  .chart-panel {
    padding: 20px 16px;
  }
  .chart {
    gap: 6px;
  }
  .month-label {
    font-size: 9px;
  }
  .bar {
    border-radius: 4px 4px 0 0;
  }
}
</style>
