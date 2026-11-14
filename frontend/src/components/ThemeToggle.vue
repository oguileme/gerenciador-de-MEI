<script setup lang="ts">
import { computed } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { isDark, toggleTheme } = useTheme()
const label = computed(() => isDark.value ? 'Ativar modo claro' : 'Ativar modo escuro')
</script>

<template>
  <button
    class="theme-toggle" type="button" :aria-label="label" :title="label"
    :aria-pressed="isDark" @click="toggleTheme"
  >
    <svg v-if="isDark" viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="12" cy="12" r="4" />
      <path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
    </svg>
    <svg v-else viewBox="0 0 24 24" aria-hidden="true">
      <path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z" />
    </svg>
    <span>{{ isDark ? 'Modo claro' : 'Modo escuro' }}</span>
  </button>
</template>

<style scoped>
.theme-toggle {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 11px;
  border: 1px solid var(--color-border);
  border-radius: 9px;
  color: var(--color-forest);
  background: var(--color-surface-soft);
  font-size: 12px;
  font-weight: 700;
}

.theme-toggle:hover {
  border-color: var(--color-forest);
  background: var(--color-sage);
}

svg {
  width: 18px;
  height: 18px;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

@media (max-width: 480px) {
  .theme-toggle span {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip-path: inset(50%);
  }
}
</style>
