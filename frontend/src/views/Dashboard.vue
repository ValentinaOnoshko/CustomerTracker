<template>
  <div>
    <h1>Dashboard</h1>
    <div v-if="user && user.role === 'client'">
      <TicketForm @ticket-created="handleTicketCreated" />
      <div v-if="loading" class="loading">Loading tickets...</div>
      <TicketList v-else :tickets="tickets" @ticket-updated="loadTickets" />
    </div>
    <div v-else-if="!user" class="loading">
      Loading...
    </div>
  </div>
</template>

<script>
import api from '@/api'
import TicketForm from '@/components/TicketForm.vue'
import TicketList from '@/components/TicketList.vue'

export default {
  name: 'Dashboard',
  components: {
    TicketForm,
    TicketList
  },
  data() {
    return {
      tickets: [],
      user: null,
      loading: false
    }
  },
  async created() {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      try {
        this.user = JSON.parse(userStr)
      } catch (e) {
        console.error('Error parsing user:', e)
        this.user = null
      }
    }
    await new Promise(resolve => setTimeout(resolve, 100))
    await this.loadTickets()
  },
  methods: {
    async loadTickets() {
      this.loading = true
      try {
        const response = await api.get('/tickets')
        console.log('Tickets response:', response.data)
        let ticketsData = []
        if (response.data && response.data.success && response.data.data) {
          ticketsData = response.data.data
        } else if (response.data && response.data.data) {
          ticketsData = response.data.data
        } else if (Array.isArray(response.data)) {
          ticketsData = response.data
        } else {
          console.warn('Unexpected response structure:', response.data)
        }

        this.tickets = ticketsData.map((ticket, index) => ({
          ...ticket,
          displayNumber: index + 1
        }))
        console.log('Loaded tickets:', this.tickets.length)
      } catch (error) {
        console.error('Error loading tickets:', error)
        if (error.response) {
          console.error('Response data:', error.response.data)
          console.error('Response status:', error.response.status)
        }
        this.tickets = []
      } finally {
        this.loading = false
      }
    },
    async handleTicketCreated() {
      console.log('Ticket created, reloading tickets...')
      await new Promise(resolve => setTimeout(resolve, 500))
      await this.loadTickets()
    }
  }
}
</script>

<style scoped>
.loading {
  text-align: center;
  padding: 20px;
  color: #6c757d;
}
</style>
