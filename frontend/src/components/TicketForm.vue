<template>
  <div class="ticket-form-container">
    <h2>Create New Ticket</h2>
    <form @submit.prevent="handleSubmit" class="ticket-form">
      <div class="form-group">
        <label for="title">Title</label>
        <input
          type="text"
          id="title"
          v-model="form.title"
          required
          class="form-control"
        />
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          required
          class="form-control"
          rows="4"
        ></textarea>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Creating...' : 'Create Ticket' }}
      </button>

      <div v-if="message" class="alert" :class="messageType">
        {{ message }}
      </div>
    </form>
  </div>
</template>

<script>
import api from '@/api'

export default {
  name: 'TicketForm',
  emits: ['ticket-created'],
  data() {
    return {
      form: {
        title: '',
        description: ''
      },
      loading: false,
      message: null,
      messageType: 'alert-success'
    }
  },
  methods: {
    async handleSubmit() {
      this.loading = true
      this.message = null

      try {
        const response = await api.post('/tickets', this.form)
        console.log('Ticket created response:', response.data)
        this.message = 'Ticket created successfully!'
        this.messageType = 'alert-success'
        this.form = { title: '', description: '' }
        setTimeout(() => {
          this.$emit('ticket-created')
        }, 100)
      } catch (error) {
        console.error('Error creating ticket:', error)
        this.message = error.response?.data?.error || error.response?.data?.message || 'Failed to create ticket'
        this.messageType = 'alert-danger'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.ticket-form-container {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.btn-primary {
  background-color: #28a745;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.alert {
  padding: 10px;
  margin-top: 10px;
  border-radius: 4px;
  font-size: 14px;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
</style>
