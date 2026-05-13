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
        component: () => import('@/views/BucleView.vue'),
        meta: { title: 'Bucle' },
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
        path: 'calendar',
        name: 'calendar',
        component: () => import('@/views/CalendarView.vue'),
        meta: { title: 'Calendario' },
      },
      {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/views/NotificationsView.vue'),
        meta: { title: 'Notificaciones' },
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
        path: 'categories',
        name: 'categories',
        component: () => import('@/views/CategoriesView.vue'),
        meta: { title: 'Categorías' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  document.title = `${to.meta.title || 'App'} | Bucle`
})

export default router