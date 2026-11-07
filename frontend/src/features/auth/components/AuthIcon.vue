<script setup lang="ts">
/** Ícones vetoriais compartilhados pelos controles de autenticação. */
defineProps<{
  name: 'email' | 'lock' | 'user' | 'eye' | 'eye-off' | 'check' | 'leaf'
}>()

const paths = {
  email: 'M4 5h16v14H4z M4 6l8 6 8-6',
  lock: 'M5 10h14v11H5z M8 10V6a4 4 0 0 1 8 0v4 M12 14v3',
  user: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0 M4 21v-2a8 8 0 0 1 16 0v2',
  eye: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12 M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
  'eye-off': 'M3 3l18 18 M10 5.2a11 11 0 0 1 2-.2c6.5 0 10 7 10 7a19 19 0 0 1-3 3.8 M6 6.3A21 21 0 0 0 2 12s3.5 7 10 7a11 11 0 0 0 5-1.2 M9.9 9.9a3 3 0 0 0 4.2 4.2',
  check: 'M5 12l4 4L19 6',
  leaf: 'M5 19C-1 9 9 3 21 3c0 12-6 21-16 16Z M4 21 15 10',
}
</script>

<template>
  <svg
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
  >
    <path :d="paths[name]" />
  </svg>
</template>

<style scoped>
svg {
  display: block;
  width: 20px;
  height: 20px;
  flex: 0 0 auto;
}
</style>
