<template>
  <div>
    <h1>Manage Statuses</h1>

    <div class="form-section">
      <h3>Add New Status</h3>
      <form @submit.prevent="createStatus" class="status-form">
        <div class="form-group">
          <input
            type="text"
            v-model="newStatus.name"
            placeholder="Status name"
            required
            class="form-control"
          />
        </div>
        <button type="submit" class="btn btn-primary">Add Status</button>
      </form>
    </div>

    <div class="list-section">
      <h3>Existing Statuses</h3>
      <table class="status-table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(status, index) in statuses" :key="status.id">
          <td>{{ index + 1 }}</td>
          <td>
            <input
              v-if="editingId === status.id"
              v-model="editingStatus.name"
              class="form-control"
            />
            <span v-else>{{ status.name }}</span>
          </td>
          <td>
            <template v-if="editingId === status.id">
              <button @click="saveStatus(status.id)" class="btn btn-sm btn-success">Save</button>
              <button @click="cancelEdit" class="btn btn-sm btn-secondary">Cancel</button>
            </template>
            <template v-else>
              <button @click="startEdit(status)" class="btn btn-sm btn-primary">Edit</button>
              <button @click="deleteStatus(status.id)" class="btn btn-sm btn-danger">Delete</button>
            </template>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import api from '@/api'

export default {
  name: 'AdminStatuses',
  data() {
    return {
      statuses: [],
      newStatus: { name: '' },
      editingId: null,
      editingStatus: { name: '' }
    }
  },
  async created() {
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
    async createStatus() {
      try {
        await api.post('/statuses', this.newStatus)
        this.newStatus = { name: '' }
        await this.loadStatuses()
      } catch (error) {
        console.error('Error creating status:', error)
      }
    },
    startEdit(status) {
      this.editingId = status.id
      this.editingStatus = { ...status }
    },
    cancelEdit() {
      this.editingId = null
      this.editingStatus = { name: '' }
    },
    async saveStatus(id) {
      try {
        await api.put(`/statuses/${id}`, this.editingStatus)
        this.editingId = null
        await this.loadStatuses()
      } catch (error) {
        console.error('Error updating status:', error)
      }
    },
    async deleteStatus(id) {
      if (confirm('Are you sure you want to delete this status?')) {
        try {
          await api.delete(`/statuses/${id}`)
          await this.loadStatuses()
        } catch (error) {
          console.error('Error deleting status:', error)
        }
      }
    }
  }
}
</script>

<style scoped>
.form-section,
.list-section {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.status-form {
  display: flex;
  gap: 10px;
  align-items: center;
}

.form-group {
  flex: 1;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.btn {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.status-table {
  width: 100%;
  border-collapse: collapse;
}

.status-table th,
.status-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.status-table th {
  background-color: #f8f9fa;
}
</style>
