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
            
            <v-btn v-if="group.is_member" color="success" outlined @click="goToGroupChat(group.group_id)">
                เข้าสู่ห้องแชท
            </v-btn>

            <v-btn v-else color="primary" @click="openJoinConfirmDialog(group)">
                เข้าร่วม
            </v-btn>

          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <create-group-form v-model="createGroupDialog" @group-created="handleGroupCreated" />

    <v-dialog v-model="joinConfirmDialog" max-width="400">
        <v-card>
            <v-card-title class="text-h5">
                ยืนยันการเข้าร่วมกลุ่ม
            </v-card-title>
            <v-card-text>
                คุณต้องการส่งคำขอเพื่อเข้าร่วมกลุ่ม <strong>"{{ groupToJoin.group_name }}"</strong> ใช่หรือไม่?
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red darken-1" text @click="joinConfirmDialog = false">
                    <v-icon left>mdi-close</v-icon>
                    ไม่
                </v-btn>
                <v-btn color="green darken-1" text @click="confirmJoinGroup">
                    <v-icon left>mdi-check</v-icon>
                    ตกลง
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

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

            // 💡 Data ใหม่สำหรับควบคุม Pop-up
            joinConfirmDialog: false,
            groupToJoin: {} 
        }
    },
    mounted() {
        this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
        this.fetchGroups();
    },
    methods: {
        async fetchGroups() {
            try {
                // ส่ง user_id ไปกับ request เพื่อให้ backend รู้ว่าเราเป็นสมาชิกกลุ่มไหนแล้วบ้าง
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
            this.fetchGroups(); // โหลดข้อมูลกลุ่มใหม่หลังสร้างเสร็จ
        },
        goToGroupChat(groupId) {
            this.$router.push(`/groups/${groupId}`);
        },

        // 💡 Method ใหม่สำหรับเปิด Pop-up
        openJoinConfirmDialog(group) {
            this.groupToJoin = group;
            this.joinConfirmDialog = true;
        },

        // 💡 Method ใหม่สำหรับส่งคำขอหลังจากกด "ตกลง" ใน Pop-up
        async confirmJoinGroup() {
            if (!this.groupToJoin.group_id) return;

            try {
                // ยิง API ไปที่ `send_join_request.php` (ที่เราเคยสร้างไว้)
                const res = await this.$axios.post('/send_join_request.php', {
                    group_id: this.groupToJoin.group_id,
                    user_id: this.currentUserId,
                });

                if (res.data.success) {
                    alert('ส่งคำขอเข้าร่วมกลุ่มสำเร็จแล้ว');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + res.data.error);
                }

            } catch (error) {
                console.error(`Failed to join group`, error);
                alert('การเชื่อมต่อล้มเหลว ไม่สามารถส่งคำขอได้');
            } finally {
                this.joinConfirmDialog = false; // ปิด Pop-up
            }
        }
    }
}
</script>