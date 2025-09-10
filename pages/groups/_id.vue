<template>
  <v-container fluid class="fill-height pa-0">
    <div v-if="loading" class="d-flex fill-height align-center justify-center">
      <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
    </div>

    <div v-else-if="membershipStatus === 'member'" class="d-flex fill-height">
      
      <v-navigation-drawer v-if="currentUserRole === 'admin'" permanent width="300">
        <v-toolbar flat dense>
          <v-toolbar-title class="font-weight-bold subtitle-1">
            <v-icon left>mdi-account-plus-outline</v-icon>
            คำขอเข้าร่วม
          </v-toolbar-title>
        </v-toolbar>
        <v-divider></v-divider>

        <v-list dense class="pa-0">
          <div v-if="joinRequests.length === 0" class="text-center grey--text pa-4 text--darken-1">
            ไม่มีคำขอเข้าร่วม
          </div>
          <template v-for="(request, index) in joinRequests">
            <div :key="request.request_id" class="pa-2">
              <v-list-item>
                <v-list-item-avatar>
                  <v-img :src="request.avatar_url || '/default.png'"></v-img>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title>{{ request.username }}</v-list-item-title>
                </v-list-item-content>
              </v-list-item>
              <v-list-item class="mt-n2">
                  <v-spacer></v-spacer>
                  <v-btn small color="error" text @click="handleRequest(request.request_id, 'reject')">ปฏิเสธ</v-btn>
                  <v-btn small color="success" depressed @click="handleRequest(request.request_id, 'approve')">อนุมัติ</v-btn>
              </v-list-item>
            </div>
            <v-divider v-if="index < joinRequests.length - 1" :key="'divider-' + request.request_id"></v-divider>
          </template>
        </v-list>
      </v-navigation-drawer>

      <div class="flex-grow-1 d-flex flex-column">
        <v-card class="d-flex flex-column fill-height" elevation="0" tile>
            <v-card-title class="grey lighten-4 py-2 flex-shrink-0">
              <span class="font-weight-bold ml-2">{{ group.group_name || 'Group Chat' }}</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text ref="messageContainer" class="flex-grow-1 overflow-y-auto pa-4">
              <div v-for="msg in messages" :key="msg.message_id" class="d-flex my-2 align-end" :class="{'justify-end': msg.user_id == currentUserId}">
                <v-avatar v-if="msg.user_id != currentUserId" size="32" class="mr-2">
                  <v-img :src="msg.avatar_url || '/default.png'"></v-img>
                </v-avatar>
                <div class="d-flex flex-column" :class="{'align-end': msg.user_id == currentUserId}">
                  <div v-if="msg.user_id != currentUserId" class="caption grey--text px-3">{{ msg.username }}</div>
                  <div class="message-bubble" :class="{'primary white--text': msg.user_id == currentUserId, 'grey lighten-3': msg.user_id != currentUserId}">
                    {{ msg.message }}
                  </div>
                </div>
              </div>
              <div v-if="messages.length === 0" class="text-center grey--text mt-5">
                เริ่มต้นการสนทนาได้เลย!
              </div>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions class="pa-2 grey lighten-4 flex-shrink-0 align-start">
              <v-textarea v-model="newMessage" placeholder="พิมพ์ข้อความ..." hide-details outlined dense rows="1" auto-grow @keydown.enter.prevent="sendMessage"></v-textarea>
              <v-btn class="ml-2 mt-1" color="primary" icon @click="sendMessage" :disabled="!newMessage.trim()"><v-icon>mdi-send</v-icon></v-btn>
            </v-card-actions>
        </v-card>
      </div>

      <v-navigation-drawer v-if="currentUserRole === 'admin'" permanent right width="300">
        <v-toolbar flat dense>
          <v-toolbar-title class="font-weight-bold subtitle-1">
            <v-icon left>mdi-account-group-outline</v-icon>
            สมาชิกทั้งหมด
          </v-toolbar-title>
        </v-toolbar>
        <v-divider></v-divider>
        <v-list dense>
          <v-list-item v-for="member in groupMembers" :key="member.user_id">
            <v-list-item-avatar>
              <v-img :src="member.avatar_url || '/default.png'"></v-img>
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title>{{ member.username }}</v-list-item-title>
              <v-list-item-subtitle>{{ member.role }}</v-list-item-subtitle>
            </v-list-item-content>
            <v-list-item-action v-if="member.user_id !== currentUserId && member.role !== 'admin'">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="kickMember(member.user_id, member.username)">
                    <v-icon small>mdi-account-remove-outline</v-icon>
                  </v-btn>
                </template>
                <span>ลบ {{ member.username }} ออกจากกลุ่ม</span>
              </v-tooltip>
            </v-list-item-action>
          </v-list-item>
        </v-list>
      </v-navigation-drawer>

    </div>

    <div v-else class="d-flex fill-height align-center justify-center">
       <v-card class="mx-auto pa-5 text-center" max-width="500">
        <v-icon size="80" color="grey lighten-1">mdi-account-lock-outline</v-icon>
        <v-card-title class="headline justify-center">
          คุณยังไม่ได้เป็นสมาชิกของกลุ่มนี้
        </v-card-title>
        <v-card-text v-if="membershipStatus === 'not_member'">
          กรุณาส่งคำขอเพื่อเข้าร่วมกลุ่ม
        </v-card-text>
        <v-card-text v-else-if="membershipStatus === 'pending'">
          คำขอเข้าร่วมกลุ่มของคุณกำลังรอการอนุมัติ
        </v-card-text>
        <v-card-actions class="justify-center mt-3">
          <v-btn v-if="membershipStatus === 'not_member' || membershipStatus === 'rejected'" color="primary" @click="sendJoinRequest" :loading="requesting" large>
            <v-icon left>mdi-email-send-outline</v-icon>
            ส่งคำขอเข้ากลุ่ม
          </v-btn>
          <v-btn text to="/groups" large>
            <v-icon left>mdi-arrow-left</v-icon>
            กลับหน้ารวมกลุ่ม
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </v-container>
</template>

<script>
export default {
  name: 'GroupPage',
  data() {
    return {
      // สถานะทั่วไป
      loading: true,
      requesting: false,
      membershipStatus: null,
      currentUserRole: null, // 'admin' or 'member'
      
      // ข้อมูลผู้ใช้และกลุ่ม
      currentUserId: null,
      groupId: this.$route.params.id,
      group: {},
      
      // สถานะของแชทและส่วนจัดการ
      messages: [],
      newMessage: '',
      joinRequests: [],
      groupMembers: [],
      polling: null
    }
  },
  async created() {
    if (process.client) {
      const userIdFromStorage = localStorage.getItem('edukris_id');
      if (!userIdFromStorage) {
        this.$router.push('/index-login');
        return;
      }
      this.currentUserId = parseInt(userIdFromStorage);
      await this.initializePage();
    }
  },
  beforeDestroy() {
    clearInterval(this.polling);
  },
  methods: {
    async initializePage() {
      this.loading = true;
      try {
        const statusRes = await this.$axios.get(`/check_group_status.php?group_id=${this.groupId}&user_id=${this.currentUserId}`);
        this.membershipStatus = statusRes.data.status;
        this.currentUserRole = statusRes.data.role;

        if (this.membershipStatus === 'member') {
          const detailsRes = await this.$axios.get(`/get_group_details.php?group_id=${this.groupId}`);
          this.group = detailsRes.data.group;
          this.startChat();

          if (this.currentUserRole === 'admin') {
            this.fetchAdminData();
          }
        }
      } catch (error) {
        console.error("Initialization failed", error);
        this.membershipStatus = 'error';
      } finally {
        this.loading = false;
      }
    },
    startChat() {
      this.fetchMessages();
      this.polling = setInterval(this.fetchMessages, 3000);
    },
    async fetchAdminData() {
      this.fetchJoinRequests();
      this.fetchGroupMembers();
    },
    async fetchMessages() { /* ... โค้ดเหมือนเดิม ... */ },
    async sendMessage() { /* ... โค้ดเหมือนเดิม ... */ },
    scrollToBottom() { /* ... โค้ดเหมือนเดิม ... */ },
    async fetchJoinRequests() {
      try {
        const res = await this.$axios.get(`/get_join_requests.php?group_id=${this.groupId}`);
        if (res.data.status === 'success') this.joinRequests = res.data.data;
      } catch (e) { console.error('Failed to fetch join requests', e); }
    },
    async fetchGroupMembers() {
      try {
        const res = await this.$axios.get(`/get_group_members.php?group_id=${this.groupId}`);
        if (res.data.status === 'success') this.groupMembers = res.data.data;
      } catch (e) { console.error('Failed to fetch group members', e); }
    },
    async handleRequest(requestId, action) {
      try {
        await this.$axios.post('/handle_join_request.php', { request_id: requestId, action: action });
        this.fetchJoinRequests();
        if (action === 'approve') {
          this.fetchGroupMembers();
        }
      } catch (e) { alert(`Failed to ${action} request.`); }
    },
    async kickMember(userIdToKick, username) {
      if (confirm(`คุณแน่ใจหรือไม่ว่าจะลบ ${username} ออกจากกลุ่ม?`)) {
        try {
          await this.$axios.post('/kick_group_member.php', { group_id: this.groupId, user_to_kick_id: userIdToKick });
          this.fetchGroupMembers();
        } catch (e) { alert('Failed to remove member.'); }
      }
    },
    async sendJoinRequest() { /* ... โค้ดเหมือนเดิม ... */ },
  }
}
</script>

<style scoped>
.fill-height {
  height: calc(100vh - 64px); 
}
.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  max-width: 100%;
  word-wrap: break-word;
}
/* ทำให้ v-navigation-drawer ไม่มีเส้นขอบขวา/ซ้าย ที่ซ้อนกัน */
.v-navigation-drawer--permanent:not(.v-navigation-drawer--right) {
  border-right: 1px solid rgba(0, 0, 0, 0.12) !important;
}
.v-navigation-drawer--right.v-navigation-drawer--permanent {
  border-left: 1px solid rgba(0, 0, 0, 0.12) !important;
}
</style>