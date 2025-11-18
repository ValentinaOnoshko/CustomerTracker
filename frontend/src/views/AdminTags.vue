<template>
  <div>
    <h1>Manage Tags</h1>

    <div class="form-section">
      <h3>Add New Tag</h3>
      <form @submit.prevent="createTag" class="tag-form">
        <div class="form-group">
          <input
            type="text"
            v-model="newTag.name"
            placeholder="Tag name"
            required
            class="form-control"
          />
        </div>
        <button type="submit" class="btn btn-primary">Add Tag</button>
      </form>
    </div>

    <div class="list-section">
      <h3>Existing Tags</h3>
      <table class="tag-table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(tag, index) in tags" :key="tag.id">
          <td>{{ index + 1 }}</td>
          <td>
            <input
              v-if="editingId === tag.id"
              v-model="editingTag.name"
              class="form-control"
            />
            <span v-else>{{ tag.name }}</span>
          </td>
          <td>
            <template v-if="editingId === tag.id">
              <button @click="saveTag(tag.id)" class="btn btn-sm btn-success">Save</button>
              <button @click="cancelEdit" class="btn btn-sm btn-secondary">Cancel</button>
            </template>
            <template v-else>
              <button @click="startEdit(tag)" class="btn btn-sm btn-primary">Edit</button>
              <button @click="deleteTag(tag.id)" class="btn btn-sm btn-danger">Delete</button>
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
  name: 'AdminTags',
  data() {
    return {
      tags: [],
      newTag: { name: '' },
      editingId: null,
      editingTag: { name: '' }
    }
  },
  async created() {
    await this.loadTags()
  },
  methods: {
    async loadTags() {
      try {
        const response = await api.get('/tags')
        this.tags = response.data.data
      } catch (error) {
        console.error('Error loading tags:', error)
      }
    },
    async createTag() {
      try {
        await api.post('/tags', this.newTag)
        this.newTag = { name: '' }
        await this.loadTags()
      } catch (error) {
        console.error('Error creating tag:', error)
      }
    },
    startEdit(tag) {
      this.editingId = tag.id
      this.editingTag = { ...tag }
    },
    cancelEdit() {
      this.editingId = null
      this.editingTag = { name: '' }
    },
    async saveTag(id) {
      try {
        await api.put(`/tags/${id}`, this.editingTag)
        this.editingId = null
        await this.loadTags()
      } catch (error) {
        console.error('Error updating tag:', error)
      }
    },
    async deleteTag(id) {
      if (confirm('Are you sure you want to delete this tag?')) {
        try {
          await api.delete(`/tags/${id}`)
          await this.loadTags()
        } catch (error) {
          console.error('Error deleting tag:', error)
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

.tag-form {
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

.tag-table {
  width: 100%;
  border-collapse: collapse;
}

.tag-table th,
.tag-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.tag-table th {
  background-color: #f8f9fa;
}
</style>
