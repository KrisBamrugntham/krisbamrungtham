<template>
  <v-container>
    <v-card>
      <v-card-title>
        รายชื่อผู้ใช้
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="fetchUsers">โหลดใหม่</v-btn>
      </v-card-title>

      <v-card-text>
        <v-alert v-if="error" type="error" outlined dense>{{ error }}</v-alert>

        <v-progress-linear v-if="loading" indeterminate color="primary"></v-progress-linear>

        <v-data-table
          v-if="users.length > 0"
          :headers="headers"
          :items="users"
          :items-per-page="5"
          class="elevation-1 mt-4"
        ></v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      loading: false,
      error: null,
      headers: [
        { text: 'User ID', value: 'user_id' },
        { text: 'Username', value: 'username' },
        { text: 'Email', value: 'email' },
        { text: 'Password Hash', value: 'password_hash' },
        { text: 'Avatar URL', value: 'avatar_url' },
        { text: 'Created At', value: 'created_at' }
      ],
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;

      try {
        const res = await fetch('http://localhost/Db/select.php');
        if (!res.ok) throw new Error('โหลดข้อมูลล้มเหลว');
        const data = await res.json();
        this.users = data;
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
