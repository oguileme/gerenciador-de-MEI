<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppBrand from '@/components/AppBrand.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import type { Mei } from '@/types/mei'
import MeiProfile from './components/MeiProfile.vue'
import CreateMeiDialog from './components/CreateMeiDialog.vue'
import { useMeiSelection } from './composables/useMeiSelection'

const router = useRouter()
const { meis, loading, error, page, lastPage, load } = useMeiSelection()
const creating = ref(false)
const addButton = ref<HTMLButtonElement | null>(null)
onMounted(() => load())

async function closeDialog() {
  creating.value = false
  await nextTick()
  addButton.value?.focus()
}

function openCreated(mei: Mei) {
  creating.value = false
  void router.push({ name: 'mei-dashboard', params: { meiId: mei.id } })
}
</script>

<template>
  <div class="selection-page">
    <header><AppBrand /><ThemeToggle /></header>
    <main aria-labelledby="selection-title">
      <h1 id="selection-title">Qual MEI você quer acessar?</h1>
      <p class="intro">Escolha seu negócio para abrir o painel.</p>
      <div class="choices" :aria-busy="loading">
        <p v-if="loading" class="feedback" role="status">Carregando seus MEIs…</p>
        <div v-else-if="error" class="feedback" role="alert">
          <p>{{ error }}</p>
          <button class="retry" @click="load()">Tentar novamente</button>
        </div>
        <template v-else>
          <p v-if="!meis.length" class="empty">Seu primeiro MEI começa aqui. Adicione seu negócio para continuar.</p>
          <ul class="profiles" aria-label="Seus MEIs">
            <li v-for="mei in meis" :key="mei.id"><MeiProfile :mei="mei" /></li>
            <li>
              <button ref="addButton" class="add-profile" @click="creating = true">
                <span class="add-icon" aria-hidden="true">
                  <svg viewBox="0 0 40 40" fill="none"><path d="M20 9v22M9 20h22" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" /></svg>
                </span>
                <span>Adicionar MEI</span>
                <span class="add-caption">Mais um negócio, tudo no lugar.</span>
              </button>
            </li>
          </ul>
          <nav v-if="lastPage > 1" class="pagination" aria-label="Páginas de MEIs">
            <button :disabled="page === 1" @click="load(page - 1)">Anterior</button>
            <span role="status">{{ page }} de {{ lastPage }}</span>
            <button :disabled="page === lastPage" @click="load(page + 1)">Próxima</button>
          </nav>
        </template>
      </div>
      <p class="footnote">Cada negócio com seu próprio espaço.</p>
    </main>
    <footer>Meu MEI. Feito para quem faz acontecer.</footer>
    <CreateMeiDialog v-if="creating" @close="closeDialog" @created="openCreated" />
  </div>
</template>

<style scoped>
.selection-page {
  display: flex;
  flex-direction: column;
  min-height: 100svh;
  background: var(--color-paper);
}

header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 36px 4.5vw;
}

main {
  width: min(1120px, 100%);
  margin: auto;
  padding: 62px 32px 76px;
  text-align: center;
}

h1 {
  margin: 0;
  font-size: clamp(30px, 4vw, 48px);
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -1.9px;
}

.intro {
  margin: 18px 0 0;
  color: var(--color-muted);
  font-size: 16px;
}

.choices {
  margin-top: 64px;
  min-height: 238px;
}

.profiles {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 44px 28px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.profiles li {
  width: 190px;
}

.add-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  padding: 0;
  border: 0;
  border-radius: 12px;
  background: transparent;
  color: var(--color-forest);
  font-weight: 700;
  font-size: 16px;
}

.add-icon {
  display: grid;
  place-items: center;
  width: 148px;
  height: 148px;
  margin-bottom: 22px;
  border: 1px dashed var(--color-border);
  border-radius: 38px;
  background: var(--color-surface-soft);
}

.add-icon svg {
  width: 40px;
  height: 40px;
}

.add-profile:hover .add-icon {
  border-style: solid;
  background: var(--color-sage);
}

.add-caption {
  max-width: 175px;
  margin-top: 5px;
  font-size: 11px;
  font-weight: 400;
  color: var(--color-muted);
}

.footnote {
  margin: 58px 0 0;
  font-size: 12px;
  color: var(--color-muted);
}

footer {
  padding: 24px;
  text-align: center;
  color: var(--color-muted);
  font-size: 11px;
}

.empty {
  max-width: 340px;
  margin: -20px auto 32px;
  color: var(--color-muted);
}

.feedback {
  padding: 40px 0;
  color: var(--color-muted);
}

.retry, .pagination button {
  min-height: 44px;
  padding: 8px 16px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-forest);
  background: var(--color-paper);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  margin-top: 40px;
}

.pagination button:disabled {
  opacity: .45;
  cursor: default;
}

@media (max-width: 600px) {
  header {
    padding: 24px;
  }
  main {
    padding: 46px 20px;
  }
  h1 {
    max-width: 360px;
    margin-inline: auto;
    letter-spacing: -1px;
  }
  .intro {
    font-size: 14px;
    margin-top: 14px;
  }
  .choices {
    margin-top: 40px;
  }
  .profiles {
    gap: 32px 16px;
  }
  .profiles li {
    width: calc(50% - 8px);
    max-width: 190px;
  }
  .add-profile {
    font-size: 14px;
  }
  .add-icon {
    width: 116px;
    height: 116px;
    border-radius: 30px;
    margin-bottom: 16px;
  }
  .add-caption {
    max-width: 130px;
    font-size: 10px;
  }
  .footnote {
    margin-top: 40px;
  }
}
</style>
