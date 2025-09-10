<template>
  <v-container fluid class="test-page-bg">
    <v-row justify="center">
      <v-col cols="12" md="10" lg="8">
        <v-card rounded="lg">
          <v-card-title>
            <v-icon left>mdi-test-tube</v-icon>
            <span class="font-weight-bold">Test Page: User Data</span>
            <v-spacer></v-spacer>
            <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
                single-line
                hide-details
                dense
                outlined
            ></v-text-field>
          </v-card-title>
          
          <v-alert v-if="error" type="error" tile dense>{{ error }}</v-alert>
          <v-progress-linear v-if="loading" indeterminate color="primary"></v-progress-linear>

          <v-data-table
            :headers="headers"
            :items="users"
            :search="search"
            :items-per-page="10"
            class="mt-2"
          ></v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'TestPage',
  data() {
    return {
      users: [],
      loading: false,
      error: null,
      search: '',
      headers: [
        { text: 'User ID', value: 'user_id', width: '100px' },
        { text: 'Username', value: 'username' },
        { text: 'Email', value: 'email' },
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
        const res = await this.$axios.get('/select_users.php'); // Changed to a more appropriate endpoint
        if (res.data.status === 'success') {
            this.users = res.data.data;
        } else {
            this.users = [];
            this.error = res.data.message || 'Could not fetch users.';
        }
      } catch (err) {
        this.error = 'Failed to connect to the server.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.test-page-bg {
    background-color: #F0F2F5;
    min-height: 100%;
}
</style>
