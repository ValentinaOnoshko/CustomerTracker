<template>
  <div class="ticket-list-container">
    <h2>My Tickets</h2>

    <div class="filters">
      <input
        type="text"
        v-model="searchTerm"
        placeholder="Search tickets..."
        class="form-control search-input"
      />
      <select v-model="statusFilter" class="form-control">
        <option value="">All Statuses</option>
        <option v-for="status in statuses" :key="status.id" :value="String(status.id)">
          {{ status.name }}
        </option>
      </select>
    </div>

    <table class="ticket-table">
      <thead>
      <tr>
        <th @click="sortBy('id')" class="sortable">
          ID
          <span v-if="sortField === 'id'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th @click="sortBy('created_at')" class="sortable">
          Created At
          <span v-if="sortField === 'created_at'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th @click="sortBy('updated_at')" class="sortable">
          Updated At
          <span v-if="sortField === 'updated_at'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th @click="sortBy('status_name')" class="sortable">
          Status
          <span v-if="sortField === 'status_name'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th @click="sortBy('title')" class="sortable">
          Title
          <span v-if="sortField === 'title'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th @click="sortBy('description')" class="sortable">
          Description
          <span v-if="sortField === 'description'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="ticket in filteredTickets" :key="ticket.id">
        <td>{{ ticket.displayNumber ?? getTicketDisplayNumber(ticket.id) }}</td>
        <td>{{ formatDate(ticket.created_at) }}</td>
        <td>{{ formatDate(ticket.updated_at) }}</td>
        <td>
            <span class="status-badge" :class="getStatusClass(ticket.status_name)">
              {{ ticket.status_name || 'N/A' }}
            </span>
        </td>
        <td>{{ ticket.title }}</td>
        <td>{{ ticket.description || 'N/A' }}</td>
        <td>
          <button @click="viewTicketDetails(ticket)" class="btn btn-sm btn-primary">View Details</button>
        </td>
      </tr>
      </tbody>
    </table>

    <div v-if="filteredTickets.length === 0" class="no-results">
      No tickets found
    </div>

    <div v-if="selectedTicket" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Ticket #{{ getTicketDisplayNumber(selectedTicket.id) }} - {{ selectedTicket.title }}</h3>
          <button @click="closeModal" class="btn btn-close">&times;</button>
        </div>
        <div class="modal-body">
          <p><strong>Description:</strong> {{ selectedTicket.description }}</p>
          <p><strong>Status:</strong> {{ selectedTicket.status_name }}</p>
          <p><strong>Created:</strong> {{ formatDate(selectedTicket.created_at) }}</p>
          <p><strong>Updated:</strong> {{ formatDate(selectedTicket.updated_at) }}</p>

          <div class="comments-section">
            <h4>Admin Responses</h4>
            <div class="comments-list">
              <div v-if="!selectedTicket.comments || selectedTicket.comments.length === 0" class="no-comments">
                <p>No responses yet.</p>
              </div>
              <div v-for="comment in (selectedTicket.comments || [])" :key="comment.id" class="comment-item">
                <div class="comment-header">
                  <strong>{{ comment.user_name || 'Admin' }}</strong>
                  <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                </div>
                <div class="comment-message">{{ comment.message }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api'

/**
 * @typedef {Object} Ticket
 * @property {number} id
 * @property {string} created_at
 * @property {string} updated_at
 * @property {string} status_name
 * @property {number} status_id
 * @property {string} title
 * @property {string} description
 * @property {Array<Object>} comments
 */

/**
 * @typedef {Object} Comment
 * @property {number} id
 * @property {string} user_name
 * @property {string} created_at
 * @property {string} message
 */
export default {
  name: 'TicketList',
  props: {
    tickets: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      searchTerm: '',
      statusFilter: '',
      sortField: 'id',
      sortOrder: 'asc',
      statuses: [],
      selectedTicket: null
    }
  },
  computed: {
    filteredTickets() {
      if (!Array.isArray(this.tickets) || this.tickets.length === 0) {
        return []
      }

      let filtered = this.tickets.filter(ticket => {
        const title = (ticket.title || '').toLowerCase()
        const description = (ticket.description || '').toLowerCase()
        const searchTerm = this.searchTerm.toLowerCase()
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm)
        const matchesStatus = !this.statusFilter || String(ticket.status_id) === String(this.statusFilter)
        return matchesSearch && matchesStatus
      })

      filtered.sort((a, b) => {
        let aVal = a[this.sortField]
        let bVal = b[this.sortField]

        if (typeof aVal === 'string') {
          aVal = aVal.toLowerCase()
          bVal = bVal.toLowerCase()
        }

        if (this.sortOrder === 'asc') {
          return aVal < bVal ? -1 : aVal > bVal ? 1 : 0
        } else {
          return aVal > bVal ? -1 : aVal < bVal ? 1 : 0
        }
      })

      return filtered
    }
  },
  async created() {
    await new Promise(resolve => setTimeout(resolve, 100))
    await this.loadStatuses()
  },
  methods: {
    async loadStatuses() {
      try {
        const response = await api.get('/statuses')
        this.statuses = response.data.data
      } catch (error) {
        console.error('Error loading statuses:', error)
      }
    },
    sortBy(field) {
      if (this.sortField === field) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortField = field
        this.sortOrder = 'asc'
      }
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      return new Date(dateString).toLocaleString()
    },
    getStatusClass(statusName) {
      if (!statusName) return 'status-unknown'
      return 'status-' + statusName.toLowerCase().replace(/\s+/g, '-')
    },
    getTicketDisplayNumber(ticketId) {
      const ticket = this.tickets.find(t => t.id === ticketId)
      return ticket?.displayNumber ?? ticketId
    },
    async viewTicketDetails(ticket) {
      try {
        const response = await api.get(`/tickets/${ticket.id}`)
        const ticketData = response.data?.data

        if (!ticketData) {
          console.error('Ticket data is missing in response:', response.data)
          this.selectedTicket = { ...ticket, comments: [] }
          return
        }

        if (!Array.isArray(ticketData.comments)) {
          ticketData.comments = ticketData.comments ? [ticketData.comments] : []
        }

        console.log('Loaded ticket comments:', ticketData.comments)

        const originalTicket = this.tickets.find(t => t.id === ticket.id)
        const displayNumber = originalTicket?.displayNumber ?? ticket.id

        this.selectedTicket = {
          ...ticketData,
          displayNumber,
          comments: ticketData.comments || []
        }

        this.$forceUpdate()
      } catch (error) {
        console.error('Error loading ticket details:', error)
        this.selectedTicket = { ...ticket, comments: [], displayNumber: ticket.displayNumber ?? ticket.id }
      }
    },
    closeModal() {
      this.selectedTicket = null
    }
  }
}
</script>

<style scoped>
.ticket-list-container {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  align-items: center;
}

.search-input {
  flex: 1;
}

.form-control {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.ticket-table {
  width: 100%;
  border-collapse: collapse;
}

.ticket-table th,
.ticket-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.ticket-table th {
  background-color: #f8f9fa;
  user-select: none;
}

.ticket-table th.sortable {
  cursor: pointer;
}

.ticket-table th.sortable:hover {
  background-color: #e9ecef;
}

.sort-indicator {
  margin-left: 5px;
  font-weight: bold;
  color: #007bff;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
}

.status-todo {
  background-color: #ffc107;
  color: #856404;
}

.status-inprogress {
  background-color: #17a2b8;
  color: #fff;
}

.status-ready-for-review {
  background-color: #6f42c1;
  color: #fff;
}

.status-done {
  background-color: #28a745;
  color: #fff;
}

.status-unknown {
  background-color: #6c757d;
  color: #fff;
}

.no-results {
  text-align: center;
  padding: 40px;
  color: #6c757d;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 20px;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
  display: inline-block;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #333;
}

.comments-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.comments-list {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 15px;
}

.comment-item {
  background: #f8f9fa;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5px;
  font-size: 12px;
}

.comment-date {
  color: #6c757d;
}

.comment-message {
  color: #333;
}

.no-comments {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #eee;
  color: #6c757d;
  font-style: italic;
}
</style>

