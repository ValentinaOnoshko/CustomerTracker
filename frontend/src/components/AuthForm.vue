<template>
  <div class="auth-container">
    <form @submit.prevent="handleSubmit" class="auth-form">
      <h2>{{ isLogin ? 'Login' : 'Register' }}</h2>

      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          v-model="form.username"
          required
          class="form-control"
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          v-model="form.password"
          required
          class="form-control"
        />
      </div>

      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Loading...' : (isLogin ? 'Login' : 'Register') }}
      </button>

      <p class="auth-switch">
        {{ isLogin ? "Don't have an account?" : "Already have an account?" }}
        <a href="#" @click.prevent="toggleMode">{{ isLogin ? 'Register' : 'Login' }}</a>
      </p>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>
    </form>
  </div>
</template>

<script>
import api from '@/api'

export default {
  name: 'AuthForm',
  data() {
    return {
      isLogin: true,
      form: {
        username: '',
        password: ''
      },
      loading: false,
      error: null
    }
  },
  methods: {
    async handleSubmit() {
      this.loading = true
      this.error = null

      try {
        const endpoint = this.isLogin ? '/auth/login' : '/auth/register'
        const response = await api.post(endpoint, this.form)

        if (response.data.token) {
          localStorage.setItem('token', response.data.token)
          localStorage.setItem('user', JSON.stringify(response.data.user))

          // Dispatch event to update App component
          window.dispatchEvent(new Event('user-updated'))

          await new Promise(resolve => setTimeout(resolve, 100))

          if (response.data.user?.role === 'admin') {
            await this.$router.push('/admin/tickets')
          } else {
            await this.$router.push('/dashboard')
          }

          window.dispatchEvent(new Event('user-updated'))
        } else if (!this.isLogin) {
          this.$router.push('/login')
        }
      } catch (err) {
        console.error('Auth error:', err)

        if (err?.response) {
          const errorData = err.response.data
          this.error = errorData?.error || errorData?.message || `Server error: ${err.response.status}`
        } else if (err?.request) {
          this.error = 'Cannot connect to server. Please check if the API is running.'
        } else {
          this.error = err?.message || 'An error occurred'
        }
      } finally {
        this.loading = false
      }
    },
    toggleMode() {
      this.isLogin = !this.isLogin
      this.error = null
    }
  }
}
</script>

<style scoped>
.auth-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
}

.auth-form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-switch {
  margin-top: 15px;
  text-align: center;
}

.auth-switch a {
  color: #007bff;
  text-decoration: none;
}

.alert {
  padding: 10px;
  margin-top: 15px;
  border-radius: 4px;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>
