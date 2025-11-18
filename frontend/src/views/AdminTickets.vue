<template>
  <div>
    <h1>Admin - All Tickets</h1>

    <div class="filters">
      <input
        type="text"
        v-model="searchTerm"
        placeholder="Search tickets..."
        class="form-control search-input"
      />
      <select v-model="statusFilter" class="form-control">
        <option value="">All Statuses</option>
        <option v-for="status in statuses" :key="status.id" :value="status.id">
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
        <th @click="sortBy('user_name')" class="sortable">
          User
          <span v-if="sortField === 'user_name'" class="sort-indicator">
            {{ sortOrder === 'asc' ? '↑' : '↓' }}
          </span>
        </th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="ticket in filteredTickets" :key="ticket.id">
        <td>{{ ticket.id }}</td>
        <td>{{ formatDate(ticket.created_at) }}</td>
        <td>{{ formatDate(ticket.updated_at) }}</td>
        <td>
            <span class="status-badge" :class="getStatusClass(ticket.status_name)">
              {{ ticket.status_name }}
            </span>
        </td>
        <td>{{ ticket.title }}</td>
        <td>{{ ticket.user_name }}</td>
        <td>
          <button @click="viewTicket(ticket)" class="btn btn-sm btn-primary">View</button>
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
          <h3>Ticket #{{ selectedTicket.id }} ({{ selectedTicket.user_name }}) - {{ selectedTicket.title }}</h3>
          <button @click="closeModal" class="btn btn-close">&times;</button>
        </div>
        <div class="modal-body">
          <p><strong>Description:</strong> {{ selectedTicket.description }}</p>
          <div><strong>Status:</strong>
            <div class="status-control">
              <select v-model="selectedTicket.status_id" class="form-control status-select">
                <option v-for="status in statuses" :key="status.id" :value="status.id">
                  {{ status.name }}
                </option>
              </select>
              <button @click="updateTicket" class="btn btn-sm btn-primary">Save Status</button>
              <span v-if="statusUpdateMessage" class="status-message" :class="statusUpdateMessageType">
                {{ statusUpdateMessage }}
              </span>
            </div>
          </div>
          <p><strong>Created:</strong> {{ formatDate(selectedTicket.created_at) }}</p>
          <p><strong>Updated:</strong> {{ formatDate(selectedTicket.updated_at) }}</p>

          <div class="tags-section">
            <h4>Tags</h4>
            <div class="current-tags">
              <span v-if="!selectedTicket.tags || selectedTicket.tags.length === 0" class="no-tags">
                No tags assigned
              </span>
              <span v-for="tag in (selectedTicket.tags || [])" :key="tag" class="tag-badge">
                {{ tag }}
                <button @click="removeTag(tag)" class="tag-remove">&times;</button>
              </span>
            </div>
            <div class="add-tag">
              <select v-model="selectedTagId" class="form-control">
                <option value="">Select or create tag...</option>
                <option v-for="tag in availableTags" :key="tag.id" :value="tag.id">
                  {{ tag.name }}
                </option>
              </select>
              <input type="text" v-model="newTag" placeholder="Or create new tag..." class="form-control">
              <button @click="addTagToTicket" class="btn btn-sm btn-secondary">Add Tag</button>
            </div>
          </div>

          <div class="comments-section">
            <h4>Comments/Responses</h4>
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
            <div class="add-comment">
              <textarea v-model="newComment" placeholder="Add response to client..." class="form-control" rows="3"></textarea>
              <button @click="addComment" class="btn btn-sm btn-primary">Send Response</button>
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
 * @property {string} user_name
 * @property {string} title
 * @property {string} description
 * @property {Array<Object>} comments
 * @property {Array<string>} tags
 */

/**
 * @typedef {Object} Comment
 * @property {number} id
 * @property {string} user_name
 * @property {string} created_at
 * @property {string} message
 */

export default {
  name: 'AdminTickets',
  data() {
    return {
      tickets: [],
      statuses: [],
      searchTerm: '',
      statusFilter: '',
      sortField: 'id',
      sortOrder: 'asc',
      selectedTicket: null,
      newTag: '',
      selectedTagId: '',
      availableTags: [],
      newComment: '',
      statusUpdateMessage: '',
      statusUpdateMessageType: 'success'
    }
  },
  computed: {
    filteredTickets() {
      let filtered = this.tickets.filter(ticket => {
        const matchesSearch = ticket.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          ticket.description.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesStatus = !this.statusFilter || ticket.status_id === this.statusFilter
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
    await this.loadTickets()
    await this.loadStatuses()
    await this.loadTags()
  },
  methods: {
    async loadTickets() {
      try {
        const response = await api.get('/tickets')
        this.tickets = response.data.data
      } catch (error) {
        console.error('Error loading tickets:', error)
      }
    },
    async loadStatuses() {
      try {
        const response = await api.get('/statuses')
        this.statuses = response.data.data
      } catch (error) {
        console.error('Error loading statuses:', error)
      }
    },
    async loadTags() {
      try {
        const response = await api.get('/tags')
        this.availableTags = response.data.data || []
      } catch (error) {
        console.error('Error loading tags:', error)
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
      return new Date(dateString).toLocaleString()
    },
    getStatusClass(statusName) {
      if (!statusName) return 'status-unknown'
      return 'status-' + statusName.toLowerCase().replace(/\s+/g, '-')
    },
    async viewTicket(ticket) {
      const ticketId = ticket?.id || this.selectedTicket?.id
      if (!ticketId) {
        console.error('Cannot load ticket: ID is missing')
        return
      }

      try {
        const response = await api.get(`/tickets/${ticketId}`)
        const ticketData = response.data?.data

        if (!ticketData) {
          console.error('Ticket data is missing in response:', response.data)
          this.selectedTicket = {
            ...ticket,
            id: ticketId,
            tags: [],
            comments: []
          }
          return
        }

        if (!ticketData.id) {
          ticketData.id = ticketId
        }
        if (!Array.isArray(ticketData.tags)) {
          ticketData.tags = ticketData.tags ? [ticketData.tags] : []
        }
        if (!Array.isArray(ticketData.comments)) {
          ticketData.comments = ticketData.comments ? [ticketData.comments] : []
        }
        console.log('Loaded ticket comments:', ticketData.comments)

        this.selectedTicket = {
          ...ticketData,
          tags: ticketData.tags || [],
          comments: ticketData.comments || []
        }

        this.$forceUpdate()

        this.newTag = ''
        this.selectedTagId = ''
        this.newComment = ''
      } catch (error) {
        console.error('Error loading ticket:', error)
        this.selectedTicket = {
          ...ticket,
          id: ticketId,
          tags: [],
          comments: []
        }
      }
    },
    closeModal() {
      this.selectedTicket = null
    },
    async updateTicket() {
      if (!this.selectedTicket || !this.selectedTicket.id) {
        console.error('Cannot update ticket: ID is missing')
        return
      }

      const ticketId = this.selectedTicket.id

      this.statusUpdateMessage = ''

      try {
        await api.put(`/tickets/${ticketId}`, {
          title: this.selectedTicket.title,
          description: this.selectedTicket.description,
          status_id: this.selectedTicket.status_id
        })

        this.statusUpdateMessage = 'Status updated successfully!'
        this.statusUpdateMessageType = 'success'

        setTimeout(() => {
          this.statusUpdateMessage = ''
        }, 3000)

        const index = this.tickets.findIndex(t => t.id === ticketId)
        if (index !== -1) {
          this.tickets[index].status_id = this.selectedTicket.status_id
          const status = this.statuses.find(s => s.id === this.selectedTicket.status_id)
          if (status) {
            this.tickets[index].status_name = status.name
          }
        }

        const ticketFromList = this.tickets.find(t => t.id === ticketId)
        if (ticketFromList) {
          await this.viewTicket(ticketFromList)
        } else {
          await this.viewTicket({ ...this.selectedTicket, id: ticketId })
        }
      } catch (error) {
        console.error('Error updating ticket:', error)
        this.statusUpdateMessage = 'Failed to update status'
        this.statusUpdateMessageType = 'error'
        setTimeout(() => {
          this.statusUpdateMessage = ''
        }, 3000)
      }
    },
    async addTagToTicket() {
      if (!this.selectedTicket || !this.selectedTicket.id) {
        console.error('Cannot add tag: Ticket ID is missing')
        return
      }

      const ticketId = this.selectedTicket.id
      let tagId = null

      if (this.selectedTagId) {
        tagId = this.selectedTagId
      }
      else if (this.newTag.trim()) {
        try {
          const response = await api.post('/tags', { name: this.newTag.trim() })
          tagId = response.data.data?.id
          if (!tagId && response.data.data) {
            tagId = response.data.data.id
          }
          if (tagId) {
            await this.loadTags()
          }
        } catch (error) {
          console.error('Error creating tag:', error)
          return
        }
      } else {
        return
      }

      if (tagId) {
        try {
          await api.post(`/tickets/${ticketId}/tags`, { tag_id: tagId })
          const ticketFromList = this.tickets.find(t => t.id === ticketId)
          if (ticketFromList) {
            await this.viewTicket(ticketFromList)
          } else {
            await this.viewTicket({ ...this.selectedTicket, id: ticketId })
          }
          this.newTag = ''
          this.selectedTagId = ''
        } catch (error) {
          console.error('Error adding tag to ticket:', error)
        }
      }
    },
    async removeTag(tagName) {
      if (!this.selectedTicket || !this.selectedTicket.id) {
        console.error('Cannot remove tag: Ticket ID is missing')
        return
      }

      const ticketId = this.selectedTicket.id
      const tag = this.availableTags.find(t => t.name === tagName)
      if (!tag) return

      try {
        await api.delete(`/tickets/${ticketId}/tags/${tag.id}`)
        const ticketFromList = this.tickets.find(t => t.id === ticketId)
        if (ticketFromList) {
          await this.viewTicket(ticketFromList)
        } else {
          await this.viewTicket({ ...this.selectedTicket, id: ticketId })
        }
      } catch (error) {
        console.error('Error removing tag:', error)
      }
    },
    async addComment() {
      if (!this.newComment.trim() || !this.selectedTicket || !this.selectedTicket.id) {
        if (!this.selectedTicket || !this.selectedTicket.id) {
          console.error('Cannot add comment: Ticket ID is missing')
        }
        return
      }

      const ticketId = this.selectedTicket.id

      try {
        const response = await api.post('/comments', {
          ticket_id: ticketId,
          message: this.newComment.trim()
        })
        console.log('Comment added response:', response.data)
        this.newComment = ''

        await new Promise(resolve => setTimeout(resolve, 300))

        const ticketFromList = this.tickets.find(t => t.id === ticketId)
        if (ticketFromList) {
          await this.viewTicket(ticketFromList)
        } else {
          await this.viewTicket({ ...this.selectedTicket, id: ticketId })
        }
      } catch (error) {
        console.error('Error adding comment:', error)
      }
    }
  }
}
</script>

<style scoped>
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
  margin-bottom: 20px;
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

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #333;
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

.status-control {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 5px;
}

.status-select {
  min-width: 200px;
}

.status-message {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.status-message.success {
  background-color: #d4edda;
  color: #155724;
}

.status-message.error {
  background-color: #f8d7da;
  color: #721c24;
}

.tags-section {
  margin-top: 20px;
}

.current-tags {
  margin-bottom: 10px;
  min-height: 24px;
}

.no-tags {
  color: #6c757d;
  font-style: italic;
  font-size: 14px;
}

.tag-badge {
  display: inline-block;
  background-color: #007bff;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  margin-right: 5px;
  font-size: 12px;
}

.add-tag {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.tag-remove {
  background: rgba(255, 255, 255, 0.3);
  border: none;
  color: white;
  cursor: pointer;
  margin-left: 5px;
  padding: 0 4px;
  border-radius: 2px;
}

.tag-remove:hover {
  background: rgba(255, 255, 255, 0.5);
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
  min-height: 50px;
}

.no-comments {
  padding: 15px;
  text-align: center;
  color: #6c757d;
  font-style: italic;
  background-color: #f8f9fa;
  border-radius: 4px;
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

.add-comment {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
</style>

