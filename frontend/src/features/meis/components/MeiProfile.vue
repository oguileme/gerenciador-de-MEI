<script setup lang="ts">
import { computed } from 'vue'
import type { Mei } from '@/types/mei'

const props = defineProps<{ mei: Mei }>()
const initials = computed(() => props.mei.mei_name.trim().split(/\s+/)
  .slice(0, 2).map((word) => word.charAt(0)).join('').toLocaleUpperCase('pt-BR'))
const cnpj = computed(() => props.mei.cnpj.replace(
  /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5',
))
</script>

<template>
  <RouterLink class="profile" :to="{ name: 'mei-dashboard', params: { meiId: mei.id } }">
    <span class="avatar" :class="`tone-${mei.id % 4}`" aria-hidden="true">{{ initials }}</span>
    <span class="name">{{ mei.mei_name }}</span>
    <span class="cnpj">{{ cnpj }}</span>
    <span class="sr-only">Abrir painel</span>
  </RouterLink>
</template>

<style scoped>
.profile {
  display: flex;
  align-items: center;
  flex-direction: column;
  text-decoration: none;
  border-radius: 12px;
}

.avatar {
  display: grid;
  place-items: center;
  width: 148px;
  height: 148px;
  margin-bottom: 22px;
  border: 5px solid var(--color-paper);
  border-radius: 38px;
  background: var(--color-sage);
  color: var(--color-forest);
  font-size: 46px;
  font-weight: 600;
  letter-spacing: -2px;
  box-shadow: 0 0 0 1px var(--color-border);
}

.tone-1 {
  background: #e1ebdf;
  color: #3d6145;
}

.tone-2 {
  background: #e2e9f0;
  color: #415d79;
}

.tone-3 {
  background: #efe4d6;
  color: #795a36;
}

.name {
  max-width: 190px;
  font-size: 16px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.cnpj {
  margin-top: 5px;
  font-size: 12px;
  color: var(--color-muted);
  white-space: nowrap;
}

.profile:hover .avatar, .profile:focus-visible .avatar {
  box-shadow: 0 0 0 3px var(--color-forest);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip-path: inset(50%);
  white-space: nowrap;
}

@media (max-width: 600px) {
  .avatar {
    width: 116px;
    height: 116px;
    border-radius: 30px;
    font-size: 36px;
    margin-bottom: 16px;
  }
  .name {
    font-size: 14px;
  }
  .cnpj {
    font-size: 10px;
  }
}
</style>
