import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/Login.vue'
import Dashboard from '@/views/Dashboard.vue'
import AdminTickets from '@/views/AdminTickets.vue'
import AdminStatuses from '@/views/AdminStatuses.vue'
import AdminTags from '@/views/AdminTags.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/tickets',
    name: 'AdminTickets',
    component: AdminTickets,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/statuses',
    name: 'AdminStatuses',
    component: AdminStatuses,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/tags',
    name: 'AdminTags',
    component: AdminTags,
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')
  let user = null

  try {
    user = userStr ? JSON.parse(userStr) : null
  } catch (e) {
    console.error('Error parsing user:', e)
  }

  if (to.path === '/login' && token && user) {
    if (user.role === 'admin') {
      next('/admin/tickets')
    } else {
      next('/dashboard')
    }
    return
  }

  if (to.path === '/' && token && user) {
    if (user.role === 'admin') {
      next('/admin/tickets')
    } else {
      next('/dashboard')
    }
    return
  }

  const requiresAuth = to.meta?.requiresAuth
  const requiresAdmin = to.meta?.requiresAdmin

  if (requiresAuth && !token) {
    next('/login')
  } else if (requiresAdmin && user?.role !== 'admin') {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
