<template>
  <v-container>
    <v-row>
      <v-col cols="12" class="d-flex align-center">
        <h1 class="text-h4">กลุ่มทั้งหมด</h1>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="createGroupDialog = true">
            <v-icon left>mdi-plus-circle</v-icon>
            สร้างกลุ่ม
        </v-btn>
      </v-col>

      <v-col v-for="group in groups" :key="group.group_id" cols="12" md="4">
        <v-card>
          <v-card-title>{{ group.group_name }}</v-card-title>
          <v-card-subtitle>{{ group.member_count }} members</v-card-subtitle>
          <v-card-text>{{ group.description }}</v-card-text>
          <v-card-actions>
            <v-btn v-if="!group.is_member" color="primary" @click="toggleGroupMembership(group.group_id, 'join')">เข้าร่วม</v-btn>
            
            <v-btn v-else color="success" outlined @click="goToGroupChat(group.group_id)">เข้าสู่ห้องแชท</v-btn>
            </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <create-group-form v-model="createGroupDialog" @group-created="handleGroupCreated" />
  </v-container>
</template>

<script>
import CreateGroupForm from '~/components/CreateGroupForm.vue';

export default {
    name: 'GroupsPage',
    components: { CreateGroupForm },
    data() {
        return {
            groups: [],
            currentUserId: null,
            createGroupDialog: false,
        }
    },
    mounted() {
        this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
        this.fetchGroups();
    },
    methods: {
        async fetchGroups() {
            try {
                const res = await this.$axios.get(`/get_groups.php?user_id=${this.currentUserId}`);
                if (res.data.status === 'success') {
                    this.groups = res.data.data;
                }
            } catch (error) {
                console.error("Failed to fetch groups", error);
            }
        },
        handleGroupCreated() {
            alert('สร้างกลุ่มสำเร็จ!');
            this.fetchGroups();
        },
        async toggleGroupMembership(groupId, action) {
             try {
                await this.$axios.post('/group_action.php', {
                    group_id: groupId,
                    user_id: this.currentUserId,
                    action: action
                });
                this.fetchGroups();
            } catch (error) {
                console.error(`Failed to ${action} group`, error);
            }
        },
        // --- 💡 จุดแก้ไขหลัก 💡 ---
        // สร้างเมธอดใหม่สำหรับนำทางไปยังหน้าแชทกลุ่ม
        goToGroupChat(groupId) {
            this.$router.push(`/groups/${groupId}`);
        }
        // --- จบจุดแก้ไข ---
    }
}
</script>
