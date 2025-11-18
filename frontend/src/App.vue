<template>
  <div id="app">
    <nav v-if="isLoggedIn && $route.path !== '/login'" class="navbar">
      <div class="nav-container">
        <h1>Customer Ticket Tracker</h1>
        <div class="nav-links">
          <router-link to="/dashboard" v-if="user && user.role === 'client' && $route.path !== '/dashboard'">My Tickets</router-link>
          <router-link to="/admin/tickets" v-if="user && user.role === 'admin'">All Tickets</router-link>
          <router-link to="/admin/statuses" v-if="user && user.role === 'admin'">Statuses</router-link>
          <router-link to="/admin/tags" v-if="user && user.role === 'admin'">Tags</router-link>
          <button @click="logout" class="btn btn-logout">Logout</button>
        </div>
      </div>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      userData: null,
      isLoggedIn: false
    }
  },
  computed: {
    user() {
      return this.userData
    }
  },
  watch: {
    '$route'() {
      this.updateUser()
      this.updateLoginStatus()
    }
  },
  mounted() {
    this.updateUser()
    this.updateLoginStatus()
    window.addEventListener('storage', this.handleStorageChange)
    window.addEventListener('user-updated', this.handleUserUpdated)
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.handleStorageChange)
    window.removeEventListener('user-updated', this.handleUserUpdated)
  },
  methods: {
    updateUser() {
      const userStr = localStorage.getItem('user')
      if (userStr) {
        try {
          this.userData = JSON.parse(userStr)
        } catch (e) {
          console.error('Error parsing user:', e)
          this.userData = null
        }
      } else {
        this.userData = null
      }
    },
    updateLoginStatus() {
      this.isLoggedIn = !!localStorage.getItem('token')
    },
    handleStorageChange() {
      this.updateUser()
      this.updateLoginStatus()
    },
    handleUserUpdated() {
      this.updateUser()
      this.updateLoginStatus()
      // Force Vue to re-render
      this.$forceUpdate()
    },
    logout() {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      this.userData = null
      this.isLoggedIn = false
      this.$router.push('/login')
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  background-color: #f5f5f5;
}

.navbar {
  background-color: #343a40;
  color: white;
  padding: 1rem 0;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
}

.nav-container h1 {
  font-size: 1.5rem;
}

.nav-links {
  display: flex;
  gap: 20px;
  align-items: center;
}

.nav-links a {
  color: white;
  text-decoration: none;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  background-color: #495057;
}

.btn-logout {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-logout:hover {
  background-color: #c82333;
}

main {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}
</style>
