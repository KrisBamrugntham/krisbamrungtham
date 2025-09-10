<template>
  <v-container>
    <!-- Header -->
    <v-row>
      <v-col cols="12">
        <v-sheet rounded="lg" class="pa-8 text-center" color="primary" dark>
          <h1 class="text-h3 font-weight-bold">ค้นหากลุ่มที่ใช่สำหรับคุณ</h1>
          <p class="text-h6 mt-2 font-weight-light">เข้าร่วมชุมชน, แบ่งปันความรู้, และเติบโตไปด้วยกัน</p>
        </v-sheet>
      </v-col>
    </v-row>

    <!-- Controls -->
    <v-row class="my-4 align-center">
        <v-col cols="12" md="8">
            <v-text-field
                v-model="search"
                label="ค้นหากลุ่ม..."
                prepend-inner-icon="mdi-magnify"
                hide-details
                outlined
                dense
            ></v-text-field>
        </v-col>
        <v-col cols="12" md="4" class="text-right">
            <v-btn color="primary" large @click="createGroupDialog = true">
                <v-icon left>mdi-plus-circle-outline</v-icon>
                สร้างกลุ่มใหม่
            </v-btn>
        </v-col>
    </v-row>

    <!-- Group Cards -->
    <v-row>
      <v-col v-if="filteredGroups.length === 0" cols="12" class="text-center grey--text mt-8">
          <p>ไม่พบกลุ่มที่ตรงกับการค้นหา</p>
      </v-col>
      <v-col v-for="group in filteredGroups" :key="group.group_id" cols="12" md="4">
        <v-card class="group-card" hover :to="group.is_member ? `/groups/${group.group_id}` : null" @click.native="!group.is_member && openJoinConfirmDialog(group)">
          <v-img
            :src="group.image_url || 'https://picsum.photos/seed/' + group.group_id + '/500/300'"
            height="150px"
            class="white--text align-end"
            gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.7)"
          >
            <v-card-title class="pb-2">{{ group.group_name }}</v-card-title>
          </v-img>
          
          <v-card-text class="pt-4">
            <div class="d-flex align-center mb-3">
                <v-icon small class="mr-2">mdi-account-group-outline</v-icon>
                <span class="body-2">{{ group.member_count }} members</span>
            </div>
            <p class="description-text">{{ group.description }}</p>
          </v-card-text>

          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn v-if="group.is_member" color="success" text>
                <v-icon left>mdi-arrow-right-bold-circle-outline</v-icon>
                เข้ากลุ่ม
            </v-btn>
            <v-btn v-else color="primary" outlined>
                <v-icon left>mdi-plus</v-icon>
                เข้าร่วม
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <create-group-form v-model="createGroupDialog" @group-created="handleGroupCreated" />

    <v-dialog v-model="joinConfirmDialog" max-width="450">
      <v-card rounded="lg" class="pa-4">
        <v-card-title class="text-h5 justify-center">
          ยืนยันการเข้าร่วมกลุ่ม
        </v-card-title>
        <v-card-text class="text-center mt-2">
          คุณต้องการส่งคำขอเพื่อเข้าร่วมกลุ่ม <br> <strong class="primary--text text-h6">"{{ groupToJoin.group_name }}"</strong> ใช่หรือไม่?
        </v-card-text>
        <v-card-actions class="mt-4">
          <v-btn text large @click="joinConfirmDialog = false">ยกเลิก</v-btn>
          <v-spacer></v-spacer>
          <v-btn color="primary" large depressed @click="confirmJoinGroup" :loading="isJoining">
            <v-icon left>mdi-check</v-icon>
            ยืนยัน
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
      joinConfirmDialog: false,
      groupToJoin: {},
      isJoining: false,
      search: '',
    }
  },
  computed: {
      filteredGroups() {
          if (!this.search) {
              return this.groups;
          }
          return this.groups.filter(group => 
              group.group_name.toLowerCase().includes(this.search.toLowerCase())
          );
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
        } else {
            this.groups = [];
        }
      } catch (error) {
        console.error("Failed to fetch groups", error);
      }
    },
    handleGroupCreated() {
      alert('สร้างกลุ่มสำเร็จ!');
      this.fetchGroups();
    },
    goToGroupChat(groupId) {
      this.$router.push(`/groups/${groupId}`);
    },
    openJoinConfirmDialog(group) {
      this.groupToJoin = group;
      this.joinConfirmDialog = true;
    },
    async confirmJoinGroup() {
      if (!this.groupToJoin.group_id) return;
      this.isJoining = true;
      try {
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
        this.isJoining = false;
        this.joinConfirmDialog = false;
      }
    }
  }
}
</script>

<style scoped>
.group-card {
    border-radius: 16px;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.description-text {
    height: 60px; /* Fixed height */
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3; /* Limit to 3 lines */
    -webkit-box-orient: vertical;  
}
</style>
