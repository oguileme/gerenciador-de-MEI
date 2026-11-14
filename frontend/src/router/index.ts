import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash }
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../features/landingPage/index.vue'),
      meta: { title: 'Meu MEI | Seu negócio em dia' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../features/auth/login.vue'),
      meta: { title: 'Entrar | Meu MEI' },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../features/auth/register.vue'),
      meta: { title: 'Criar conta | Meu MEI' },
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      redirect: { name: 'mei-selection' },
    },
    {
      path: '/meis',
      name: 'mei-selection',
      component: () => import('../features/meis/MeiSelectionView.vue'),
      meta: { requiresAuth: true, title: 'Escolha seu MEI | Meu MEI' },
    },
    {
      path: '/meis/:meiId(\\d+)/dashboard',
      name: 'mei-dashboard',
      component: () => import('../features/meis/MeiDashboardView.vue'),
      meta: { requiresAuth: true, title: 'Painel do MEI | Meu MEI' },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuth()
  try { await auth.refreshUser() } catch { /* falhas de rede são tratadas pela tela */ }
  if (to.meta.requiresAuth && !auth.user.value) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if ((to.name === 'login' || to.name === 'register') && auth.user.value) {
    return { name: 'mei-selection' }
  }
})

router.afterEach((to) => {
  document.title = typeof to.meta.title === 'string' ? to.meta.title : 'Meu MEI'
})

export default router
