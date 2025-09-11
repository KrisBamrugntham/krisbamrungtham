<template>
  <v-container fluid>
    <!-- Header -->
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold">Admin Dashboard</h1>
        <p>ภาพรวมและเครื่องมือจัดการระบบ</p>
      </v-col>
    </v-row>

    <!-- Stat Cards -->
    <v-row>
      <v-col cols="12" md="4">
        <v-card rounded="lg" class="pa-4 d-flex align-center">
          <v-avatar color="blue lighten-4" size="60" class="mr-4">
            <v-icon color="primary">mdi-account-group-outline</v-icon>
          </v-avatar>
          <div>
            <p class="text-h4 font-weight-bold mb-0">{{ users.length }}</p>
            <p class="mb-0">ผู้ใช้ทั้งหมด</p>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card rounded="lg" class="pa-4 d-flex align-center">
          <v-avatar color="green lighten-4" size="60" class="mr-4">
            <v-icon color="success">mdi-post-outline</v-icon>
          </v-avatar>
          <div>
            <p class="text-h4 font-weight-bold mb-0">{{ posts.length }}</p>
            <p class="mb-0">โพสต์ทั้งหมด</p>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card rounded="lg" class="pa-4 d-flex align-center">
          <v-avatar color="orange lighten-4" size="60" class="mr-4">
            <v-icon color="orange">mdi-google-circles-communities</v-icon>
          </v-avatar>
          <div>
            <p class="text-h4 font-weight-bold mb-0">{{ groups.length }}</p>
            <p class="mb-0">กลุ่มทั้งหมด</p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-row class="mt-4">
      <v-col cols="12" md="3">
        <v-card rounded="lg">
          <v-list color="transparent" class="pa-2">
            <v-subheader class="font-weight-bold">Admin Menu</v-subheader>
            <v-list-item-group v-model="selectedItem" color="primary">
              <v-list-item v-for="(item, i) in items" :key="i" class="mb-1" rounded="lg">
                <v-list-item-icon class="mr-3">
                  <v-icon v-text="item.icon"></v-icon>
                </v-list-item-icon>
                <v-list-item-content>
                  <v-list-item-title v-text="item.text"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="9">
        <v-card rounded="lg">
          <!-- User Management -->
          <div v-if="selectedItem === 0">
            <v-card-title>
              จัดการผู้ใช้งาน
              <v-spacer></v-spacer>
              <v-text-field v-model="search" append-icon="mdi-magnify" label="ค้นหาผู้ใช้..." single-line hide-details dense outlined></v-text-field>
            </v-card-title>
            <v-data-table :headers="userHeaders" :items="users" :search="search" :items-per-page="10">
              <template v-slot:item.status="{ item }">
                <v-chip :color="getStatusColor(item.status)" dark small>{{ item.status }}</v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn icon small class="mr-2" @click="editUser(item)">
                  <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn icon small @click="openDeleteDialog(item, 'user')">
                  <v-icon small>mdi-delete-outline</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </div>

          <!-- Post Management -->
          <div v-if="selectedItem === 1">
            <v-card-title>
              จัดการโพสต์
              <v-spacer></v-spacer>
              <v-text-field v-model="postSearch" append-icon="mdi-magnify" label="ค้นหาโพสต์..." single-line hide-details dense outlined></v-text-field>
            </v-card-title>
            <v-data-table :headers="postHeaders" :items="posts" :search="postSearch" :items-per-page="10">
                <template v-slot:item.content="{ item }">
                    <div class="text-truncate" style="max-width: 300px;">{{ item.content }}</div>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon small @click="openDeleteDialog(item, 'post')">
                        <v-icon small>mdi-delete-outline</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
          </div>

          <!-- Group Management -->
          <div v-if="selectedItem === 2">
            <v-card-title>
              จัดการกลุ่ม
              <v-spacer></v-spacer>
              <v-text-field v-model="groupSearch" append-icon="mdi-magnify" label="ค้นหากลุ่ม..." single-line hide-details dense outlined></v-text-field>
            </v-card-title>
            <v-data-table :headers="groupHeaders" :items="groups" :search="groupSearch" :items-per-page="10">
                <template v-slot:item.description="{ item }">
                    <div class="text-truncate" style="max-width: 300px;">{{ item.description }}</div>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon small @click="openDeleteDialog(item, 'group')">
                        <v-icon small>mdi-delete-outline</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
          </div>

          <!-- Other Management Sections (Coming Soon) -->
          <div v-if="selectedItem > 2" class="pa-8 text-center">
            <v-icon size="60" class="mb-4">{{ items[selectedItem].icon }}</v-icon>
            <h2>{{ items[selectedItem].text }}</h2>
            <p>Coming soon...</p>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <edit-user-form v-model="editDialog" :user="selectedUser" @user-updated="fetchUsers" />

    <!-- Generic Deletion Dialog -->
    <v-dialog v-model="deleteDialog.show" max-width="450">
      <v-card rounded="lg" class="pa-4">
        <v-card-title class="text-h5 justify-center">ยืนยันการลบ</v-card-title>
        <v-card-text class="text-center mt-2">
          คุณแน่ใจหรือไม่ว่าต้องการลบ {{ deleteDialog.type === 'user' ? 'ผู้ใช้' : deleteDialog.type === 'post' ? 'โพสต์' : 'กลุ่ม' }} 
          <strong class="red--text">{{ deleteDialog.item ? (deleteDialog.item.username || deleteDialog.item.name || `ID: ${deleteDialog.item.post_id || deleteDialog.item.group_id}`) : '' }}</strong> 
          ออกจากระบบอย่างถาวร?
        </v-card-text>
        <v-card-actions class="mt-4">
          <v-btn text large @click="closeDeleteDialog">ยกเลิก</v-btn>
          <v-spacer></v-spacer>
          <v-btn color="red" dark large depressed @click="deleteConfirmed">ยืนยันการลบ</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>

<script>
import EditUserForm from '~/components/EditUserForm.vue';

export default {
  components: { EditUserForm },
  data() {
    return {
      currentUserId: null,
      items: [
        { text: 'จัดการผู้ใช้งาน', icon: 'mdi-account-group-outline' },
        { text: 'จัดการโพสต์', icon: 'mdi-post-outline' },
        { text: 'จัดการกลุ่ม', icon: 'mdi-google-circles-communities' },
        { text: 'ตั้งค่าระบบ', icon: 'mdi-cog-outline' },
      ],
      selectedItem: 0,
      // Users
      users: [],
      search: '',
      userHeaders: [
        { text: 'ID', value: 'user_id', width: '60px' },
        { text: 'Username', value: 'username' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Status', value: 'status' },
        { text: 'Suspended Until', value: 'suspended_until' },
        { text: 'Actions', value: 'actions', sortable: false, align: 'end' },
      ],
      // Posts
      posts: [],
      postSearch: '',
      postHeaders: [
          { text: 'Post ID', value: 'post_id', width: '100px' },
          { text: 'Content', value: 'content' },
          { text: 'Author', value: 'username' },
          { text: 'Created At', value: 'created_at' },
          { text: 'Actions', value: 'actions', sortable: false, align: 'end' },
      ],
      // Groups
      groups: [],
      groupSearch: '',
      groupHeaders: [
          { text: 'Group ID', value: 'group_id', width: '100px' },
          { text: 'Name', value: 'name' },
          { text: 'Description', value: 'description' },
          { text: 'Created At', value: 'created_at' },
          { text: 'Actions', value: 'actions', sortable: false, align: 'end' },
      ],
      // Dialogs
      editDialog: false,
      selectedUser: null,
      deleteDialog: {
          show: false,
          item: null,
          type: null, // 'user' or 'post'
      },
    };
  },
  mounted() {
    const userRole = localStorage.getItem('edukris_role');
    if (userRole !== 'admin') {
      this.$router.push('/');
      return;
    }
    this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
    this.fetchUsers();
    this.fetchPosts();
    this.fetchGroups();
  },
  methods: {
    // --- Data Fetching ---
    async fetchUsers() {
      try {
        const res = await this.$axios.get('/get_users.php?all=true');
        if (res.data.status === 'success') this.users = res.data.data;
      } catch (error) { console.error('Failed to fetch users', error); }
    },
    async fetchPosts() {
      try {
        const res = await this.$axios.get(`/get_posts.php?user_id=${this.currentUserId}`);
        if (res.data.status === 'success') this.posts = res.data.data;
      } catch (error) { console.error('Failed to fetch posts', error); }
    },
    async fetchGroups() {
      try {
        const res = await this.$axios.get('/get_groups.php');
        if (res.data.status === 'success') this.groups = res.data.data;
      } catch (error) { console.error('Failed to fetch groups', error); }
    },

    // --- User Actions ---
    editUser(user) {
      this.selectedUser = { ...user };
      this.editDialog = true;
    },
    getStatusColor(status) {
      if (status === 'suspended') return 'red';
      else if (status === 'active') return 'green';
      return 'grey';
    },

    // --- Generic Deletion --- 
    openDeleteDialog(item, type) {
      this.deleteDialog.item = item;
      this.deleteDialog.type = type;
      this.deleteDialog.show = true;
    },
    closeDeleteDialog() {
      this.deleteDialog.show = false;
      this.deleteDialog.item = null;
      this.deleteDialog.type = null;
    },
    async deleteConfirmed() {
      if (!this.deleteDialog.item || !this.deleteDialog.type) return;

      if (this.deleteDialog.type === 'user') {
        await this.deleteUser();
      } else if (this.deleteDialog.type === 'post') {
        await this.deletePost();
      } else if (this.deleteDialog.type === 'group') {
        await this.deleteGroup();
      }
      
      this.closeDeleteDialog();
    },
    async deleteUser() {
        try {
            const res = await this.$axios.post('/delete_user.php', { user_id: this.deleteDialog.item.user_id });
            if (res.data.success) {
            this.fetchUsers();
            } else {
            alert('Error deleting user: ' + res.data.error);
            }
        } catch (error) {
            console.error('Failed to delete user', error);
            alert('Failed to connect to server for deletion.');
        }
    },
    async deletePost() {
        try {
            const res = await this.$axios.post('/delete_post.php', { 
                post_id: this.deleteDialog.item.post_id,
                user_id: this.currentUserId // Admin user ID
            });
            if (res.data.success) {
            this.fetchPosts();
            } else {
            alert('Error deleting post: ' + res.data.error);
            }
        } catch (error) {
            console.error('Failed to delete post', error);
            alert('Failed to connect to server for deletion.');
        }
    },
    async deleteGroup() {
        try {
            const res = await this.$axios.post('/delete_group.php', { group_id: this.deleteDialog.item.group_id });
            if (res.data.success) {
            this.fetchGroups();
            } else {
            alert('Error deleting group: ' + res.data.error);
            }
        } catch (error) {
            console.error('Failed to delete group', error);
            alert('Failed to connect to server for deletion.');
        }
    }
  },
};
</script>

<style scoped>
</style>