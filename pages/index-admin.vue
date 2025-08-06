<template>
  <v-row>
    <v-col cols="3">
      <v-sheet rounded="lg">
        <v-list color="transparent">
          <v-subheader>Admin Menu</v-subheader>
          <v-list-item-group v-model="selectedItem" color="primary">
            <v-list-item v-for="(item, i) in items" :key="i">
              <v-list-item-icon>
                <v-icon v-text="item.icon"></v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title v-text="item.text"></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-sheet>
    </v-col>

    <v-col cols="9">
      <v-sheet min-height="70vh" rounded="lg">
        <div v-if="selectedItem === 0">
          <v-card-title>
            User Management ({{ users.length }} users)
            <v-spacer></v-spacer>
            <v-btn small color="primary" @click="fetchUsers">
              <v-icon left>mdi-refresh</v-icon> Refresh
            </v-btn>
          </v-card-title>
          <v-data-table :headers="headers" :items="users" :items-per-page="10" class="elevation-1">
            <template v-slot:item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" dark small>
                {{ item.status }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon small class="mr-2" @click="editUser(item)">
                mdi-pencil
              </v-icon>
              <v-icon small @click="confirmDelete(item)">
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
        </div>
        <div v-else>
           <v-card-title>{{ items[selectedItem].text }}</v-card-title>
           <v-card-text>Coming soon...</v-card-text>
        </div>
      </v-sheet>
    </v-col>
    
    <edit-user-form v-model="editDialog" :user="selectedUser" @user-updated="fetchUsers" />
    
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Are you sure you want to delete this user?</v-card-title>
        <v-card-text>
          You are about to permanently delete <strong>{{ userToDelete ? userToDelete.username : '' }}</strong>. This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeDeleteDialog">Cancel</v-btn>
          <v-btn color="red darken-1" text @click="deleteUserConfirmed">OK</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </v-row>
</template>

<script>
import EditUserForm from '~/components/EditUserForm.vue';

export default {
  components: { EditUserForm },
  data() {
    return {
      items: [
        { text: 'จัดการผู้ใช้งาน', icon: 'mdi-account-group' },
        { text: 'จัดการโพส', icon: 'mdi-post' },
        { text: 'จัดการกลุ่ม', icon: 'mdi-google-circles-communities' },
        { text: 'จัดการบล็อก', icon: 'mdi-blogger' },
      ],
      selectedItem: 0,
      users: [],
      headers: [
        { text: 'ID', value: 'user_id' },
        { text: 'Username', value: 'username' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Status', value: 'status' },
        { text: 'Suspended Until', value: 'suspended_until' },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      editDialog: false,
      selectedUser: null,
      // --- 💡 จุดแก้ไข: เพิ่ม data สำหรับควบคุม dialog การลบ ---
      deleteDialog: false,
      userToDelete: null,
      // --- จบจุดแก้ไข ---
    };
  },
  mounted() {
    const userRole = localStorage.getItem('edukris_role');
    if (userRole !== 'admin') {
      this.$router.push('/');
      return;
    }
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const res = await this.$axios.get('/get_users.php');
        if (res.data.status === 'success') {
          this.users = res.data.data;
        }
      } catch (error) {
        console.error('Failed to fetch users', error);
      }
    },
    editUser(user) {
      this.selectedUser = { ...user };
      this.editDialog = true;
    },
    getStatusColor(status) {
      if (status === 'suspended') return 'red';
      else return 'green';
    },
    // --- 💡 จุดแก้ไข: เพิ่ม methods สำหรับจัดการการลบ ---
    confirmDelete(user) {
      this.userToDelete = user;
      this.deleteDialog = true;
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
      this.userToDelete = null;
    },
    async deleteUserConfirmed() {
      if (!this.userToDelete) return;
      try {
        const res = await this.$axios.post('/delete_user.php', { user_id: this.userToDelete.user_id });
        if (res.data.success) {
          this.fetchUsers(); // โหลดรายชื่อผู้ใช้ใหม่
        } else {
          alert('Error deleting user: ' + res.data.error);
        }
      } catch (error) {
        console.error('Failed to delete user', error);
        alert('Failed to connect to server for deletion.');
      } finally {
        this.closeDeleteDialog(); // ปิด dialog เสมอ
      }
    }
    // --- จบจุดแก้ไข ---
  },
};
</script>