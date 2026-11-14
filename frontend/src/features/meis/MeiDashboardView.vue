<script setup lang="ts">
import AppBrand from '@/components/AppBrand.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import DashboardShell from '@/features/dashboard/components/DashboardShell.vue'
import { useMeiDashboard } from './composables/useMeiDashboard'

const { mei, error, loading, load } = useMeiDashboard()
</script>

<template>
  <DashboardShell v-if="mei" :key="mei.id" :mei="mei" @updated="mei = $event" />
  <div v-else class="dashboard-handoff">
    <header><AppBrand /><div class="handoff-actions"><ThemeToggle /><RouterLink :to="{ name: 'mei-selection' }">Trocar MEI</RouterLink></div></header>
    <main>
      <p v-if="loading" role="status">Abrindo seu MEI…</p>
      <div v-else-if="error" role="alert"><p>{{ error }}</p><button @click="load">Tentar novamente</button></div>
    </main>
  </div>
</template>

<style scoped>
.dashboard-handoff {
  min-height: 100svh;
  background: var(--color-paper);
  padding: 36px 4.5vw;
}

header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.handoff-actions {
  display: flex;
  align-items: center;
  gap: 18px;
}

main {
  max-width: 640px;
  margin: 18vh auto;
  text-align: center;
  overflow-wrap: anywhere;
}

p {
  color: var(--color-muted);
}

button {
  min-height: 44px;
  padding: 8px 16px;
  background: var(--color-paper);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-forest);
}
</style>
