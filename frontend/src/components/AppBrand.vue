<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <RouterLink class="brand" to="/" aria-label="Meu MEI, página inicial">
    <span class="brand-mark" aria-hidden="true">
      <svg viewBox="0 0 32 32" fill="none">
        <path
          d="M8 23V15M16 23V9M24 23V5"
          stroke="currentColor"
          stroke-width="4"
          stroke-linecap="round"
        />
      </svg>
    </span>
    <span>meu<span class="brand-emphasis">MEI</span><span class="brand-dot">.</span></span>
  </RouterLink>
</template>

<style scoped>
.brand {
  display: inline-flex;
  align-items: center;
  align-self: flex-start;
  gap: 10px;
  color: var(--color-forest);
  font-size: 26px;
  font-weight: 500;
  letter-spacing: -1.2px;
  text-decoration: none;
}

.brand-mark {
  display: grid;
  width: 39px;
  height: 39px;
  place-items: center;
  border-radius: 11px;
  color: var(--color-sage);
  background: var(--color-forest);
}

.brand-mark svg {
  width: 26px;
  height: 26px;
}

.brand-emphasis {
  font-weight: 800;
}

.brand-dot {
  color: var(--color-leaf);
}

@media (max-width: 760px) {
  .brand {
    font-size: 23px;
  }
  .brand-mark {
    width: 34px;
    height: 34px;
    border-radius: 9px;
  }
}
</style>
