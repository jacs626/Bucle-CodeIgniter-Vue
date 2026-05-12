import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { title: 'Panel' },
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/views/CategoriesView.vue'),
        meta: { title: 'Categorías' },
      },
      {
        path: 'entities',
        name: 'entities',
        component: () => import('@/views/EntitiesView.vue'),
        meta: { title: 'Entidades' },
      },
      {
        path: 'blocks',
        name: 'blocks',
        component: () => import('@/views/BlocksView.vue'),
        meta: { title: 'Bloques' },
      },
      {
        path: 'history',
        name: 'history',
        component: () => import('@/views/HistoryView.vue'),
        meta: { title: 'Historial' },
      },
      {
        path: 'documents',
        name: 'documents',
        component: () => import('@/views/DocumentsView.vue'),
        meta: { title: 'Documentos' },
      },
      {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/views/DashboardView.vue'),
        meta: { title: 'Notificaciones' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, _from, next) => {
  document.title = `${to.meta.title || 'App'} | Bucle`
  next()
})

export default router
