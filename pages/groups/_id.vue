<template>
  <v-container fluid class="pa-0 fill-height">
    <!-- Loading State -->
    <div v-if="loading" class="d-flex fill-height align-center justify-center flex-grow-1">
      <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
    </div>

    <!-- Not a Member State -->
    <div v-else-if="membershipStatus !== 'member'" class="d-flex fill-height align-center justify-center flex-grow-1 not-member-bg">
      <v-card class="pa-4 pa-md-8 text-center" max-width="500" rounded="xl">
        <v-icon size="80" color="grey lighten-1">mdi-account-lock-outline</v-icon>
        <h2 class="text-h5 font-weight-bold mt-4">คุณยังไม่ได้เป็นสมาชิก</h2>
        <p v-if="membershipStatus === 'pending'" class="mt-2 grey--text text--darken-1">คำขอเข้าร่วมกลุ่มของคุณกำลังรอการอนุมัติ</p>
        <p v-else-if="membershipStatus === 'rejected'" class="mt-2 grey--text text--darken-1">คำขอเข้าร่วมกลุ่มของคุณถูกปฏิเสธ</p>
        <p v-else class="mt-2 grey--text text--darken-1">ส่งคำขอเพื่อเข้าร่วมพูดคุยในกลุ่มนี้</p>
        <div class="mt-6">
          <v-btn v-if="membershipStatus === 'not_member' || membershipStatus === 'rejected'" color="primary" depressed large @click="sendJoinRequest" :loading="requesting">
            <v-icon left>mdi-email-send-outline</v-icon>ส่งคำขอเข้าร่วม
          </v-btn>
          <v-btn text large to="/groups" class="ml-2">
            <v-icon left>mdi-arrow-left</v-icon>กลับหน้ารวมกลุ่ม
          </v-btn>
        </div>
      </v-card>
    </div>

    <!-- Member State -->
    <v-row v-else no-gutters class="fill-height">
      <!-- Main Chat Column -->
      <v-col cols="12" md="8" class="d-flex flex-column fill-height">
        <v-toolbar flat class="chat-header flex-shrink-0">
          <v-toolbar-title class="font-weight-bold">{{ group.group_name }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-icon class="mr-2">mdi-account-group-outline</v-icon>
          <span>{{ groupMembers.length }} Members</span>
        </v-toolbar>
        
        <v-card-text ref="messageContainer" class="flex-grow-1 overflow-y-auto pa-4 chat-area">
          <div v-for="msg in messages" :key="msg.message_id" class="message-row d-flex my-1" :class="{ 'justify-end': msg.user_id == currentUserId }">
            <v-avatar v-if="msg.user_id != currentUserId" size="32" class="mr-2 align-self-start flex-shrink-0">
              <v-img :src="msg.avatar_url || '/default.png'"></v-img>
            </v-avatar>
            <div class="message-bubble" :class="{ 'sent': msg.user_id == currentUserId, 'received': msg.user_id != currentUserId }">
              <div v-if="msg.user_id != currentUserId" class="font-weight-bold body-2 primary--text">{{ msg.username }}</div>
              <div class="message-content">{{ msg.message }}</div>
              <div class="message-time">{{ formatTime(msg.created_at) }}</div>
            </div>
          </div>
           <div v-if="messages.length === 0" class="text-center grey--text mt-10">เริ่มต้นการสนทนาในกลุ่มได้เลย!</div>
        </v-card-text>

        <div class="chat-footer pa-2 flex-shrink-0">
          <v-textarea v-model="newMessage" placeholder="พิมพ์ข้อความ..." hide-details filled rounded dense rows="1" auto-grow @keydown.enter.prevent="sendMessage"></v-textarea>
          <v-btn icon class="ml-2" color="primary" @click="sendMessage" :disabled="!newMessage.trim()"><v-icon>mdi-send</v-icon></v-btn>
        </div>
      </v-col>

      <!-- Right Info/Admin Column -->
      <v-col cols="12" md="4" class="info-sidebar">
        <div class="pa-4 text-center">
            <v-img :src="group.image_url || 'https://picsum.photos/seed/' + groupId + '/500/300'" height="150" class="rounded-lg mb-4" gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)">
                 <div class="d-flex fill-height justify-center align-center white--text text-h5 font-weight-bold">{{ group.group_name }}</div>
            </v-img>
            <p class="grey--text text--darken-2">{{ group.description }}</p>
        </div>
        <v-divider></v-divider>
        <v-tabs v-model="adminTab" background-color="transparent" grow>
            <v-tab>สมาชิก</v-tab>
            <v-tab v-if="currentUserRole === 'admin'">
                คำขอ <v-badge v-if="joinRequests.length > 0" color="pink" :content="joinRequests.length" inline class="ml-2"></v-badge>
            </v-tab>
        </v-tabs>
        <v-tabs-items v-model="adminTab" class="fill-height overflow-y-auto">
            <v-tab-item>
                <v-list>
                    <v-list-item v-for="member in groupMembers" :key="member.user_id">
                        <v-list-item-avatar><v-img :src="member.avatar_url || '/default.png'"></v-img></v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>{{ member.username }}</v-list-item-title>
                            <v-list-item-subtitle>{{ member.role }}</v-list-item-subtitle>
                        </v-list-item-content>
                        <v-list-item-action v-if="currentUserRole === 'admin' && member.user_id !== currentUserId && member.role !== 'admin'">
                            <v-btn icon small color="red lighten-1" @click="kickMember(member.user_id, member.username)"><v-icon small>mdi-account-remove-outline</v-icon></v-btn>
                        </v-list-item-action>
                    </v-list-item>
                </v-list>
            </v-tab-item>
            <v-tab-item v-if="currentUserRole === 'admin'">
                 <v-list v-if="joinRequests.length > 0">
                    <v-list-item v-for="req in joinRequests" :key="req.request_id">
                        <v-list-item-avatar><v-img :src="req.avatar_url || '/default.png'"></v-img></v-list-item-avatar>
                        <v-list-item-content><v-list-item-title>{{ req.username }}</v-list-item-title></v-list-item-content>
                        <v-list-item-action>
                            <v-btn icon small color="red lighten-1" @click="handleRequest(req.request_id, 'reject')"><v-icon small>mdi-close</v-icon></v-btn>
                            <v-btn icon small color="green lighten-1" @click="handleRequest(req.request_id, 'approve')"><v-icon small>mdi-check</v-icon></v-btn>
                        </v-list-item-action>
                    </v-list-item>
                </v-list>
                <div v-else class="text-center grey--text pa-8">ไม่มีคำขอเข้าร่วม</div>
            </v-tab-item>
        </v-tabs-items>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'GroupPage',
  data() {
    return {
      loading: true,
      requesting: false,
      membershipStatus: null,
      currentUserRole: null,
      currentUserId: null,
      groupId: this.$route.params.id,
      group: {},
      messages: [],
      newMessage: '',
      joinRequests: [],
      groupMembers: [],
      polling: null,
      adminTab: 0,
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
    formatTime(dateString) {
        if (!dateString) return '';
        const options = { hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleTimeString('th-TH', options);
    },
    async initializePage() {
      this.loading = true;
      try {
        const statusRes = await this.$axios.get(`/check_group_status.php?group_id=${this.groupId}&user_id=${this.currentUserId}`);
        this.membershipStatus = statusRes.data.status;
        this.currentUserRole = statusRes.data.role;

        if (this.membershipStatus === 'member') {
          const detailsRes = await this.$axios.get(`/get_group_details.php?group_id=${this.groupId}`);
          this.group = detailsRes.data.group;
          this.fetchGroupMembers(); // Fetch members right away
          this.startPolling();
        } else {
            clearInterval(this.polling);
        }
      } catch (error) {
        console.error("Initialization failed", error);
        this.membershipStatus = 'error';
      } finally {
        this.loading = false;
      }
    },
    startPolling() {
      this.fetchData();
      this.polling = setInterval(this.fetchData, 3000);
    },
    fetchData() {
        this.fetchMessages();
        if (this.currentUserRole === 'admin') {
            this.fetchJoinRequests();
        }
    },
    async fetchMessages() {
      try {
        const res = await this.$axios.get(`/get_group_messages.php?group_id=${this.groupId}`);
        if (res.data.status === 'success' && JSON.stringify(this.messages) !== JSON.stringify(res.data.data)) {
          this.messages = res.data.data;
          this.scrollToBottom();
        }
      } catch (error) { /* silent fail on polling */ }
    },
    async sendMessage() {
      if (!this.newMessage.trim()) return;
      const tempMessage = this.newMessage;
      this.newMessage = '';
      try {
        const res = await this.$axios.post('/send_group_message.php', { group_id: this.groupId, sender_id: this.currentUserId, message: tempMessage });
        if (res.data.success) {
          await this.fetchMessages();
        } else {
          this.newMessage = tempMessage;
          alert('Error: ' + res.data.error);
        }
      } catch (error) {
        this.newMessage = tempMessage;
        alert('ไม่สามารถส่งข้อความได้');
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messageContainer;
        if (container) container.scrollTop = container.scrollHeight;
      });
    },
    async fetchJoinRequests() {
      try {
        const res = await this.$axios.get(`/get_join_requests.php?group_id=${this.groupId}`);
        if (res.data.status === 'success') this.joinRequests = res.data.data;
      } catch (e) { /* silent fail on polling */ }
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
        if (action === 'approve') this.fetchGroupMembers();
      } catch (e) { alert(`Failed to ${action} request.`); }
    },
    async kickMember(userIdToKick, username) {
      if (confirm(`คุณแน่ใจหรือไม่ว่าจะลบ ${username} ออกจากกลุ่ม?`)) {
        try {
          await this.$axios.post('/kick_group_member.php', { group_id: this.groupId, user_to_kick_id: userIdToKick, admin_id: this.currentUserId });
          this.fetchGroupMembers();
        } catch (e) { alert('Failed to remove member.'); }
      }
    },
    async sendJoinRequest() {
      this.requesting = true;
      try {
        await this.$axios.post('/send_join_request.php', { group_id: this.groupId, user_id: this.currentUserId });
        this.membershipStatus = 'pending';
      } catch (error) {
        alert('ไม่สามารถส่งคำขอได้');
      } finally {
        this.requesting = false;
      }
    },
  }
}
</script>

<style scoped>
.fill-height { height: calc(100vh - 64px); }
.not-member-bg { background-color: #F0F2F5; }

/* Chat Area */
.chat-header { border-bottom: 1px solid #e0e0e0 !important; background-color: white; }
.chat-area { 
    background-color: #EFEAE2; 
    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARMAAAARMAAQMAAAA/3/8OAAAABlBMVEXd3d3d3d3d3d0m875OAAAAAXRSTlMAQObYZgAAAB9JREFUeN7twQENAAAAwiD7p7bHBwwAAAAg7AEnYgAB9p0s9wAAAABJRU5ErkJggg==');
}
.message-row { max-width: 75%; }
.message-bubble { padding: 6px 12px; border-radius: 12px; position: relative; word-wrap: break-word; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
.message-bubble.sent { background-color: #DCF8C6; border-top-right-radius: 0; }
.message-bubble.received { background-color: #FFFFFF; border-top-left-radius: 0; }
.message-content { padding-bottom: 16px; }
.message-time { font-size: 0.7rem; color: grey; position: absolute; bottom: 4px; right: 8px; }
.chat-footer { background-color: #F0F2F5; border-top: 1px solid #e0e0e0; display: flex; align-items: center; }

/* Info Sidebar */
.info-sidebar { background-color: white; border-left: 1px solid #e0e0e0; display: flex; flex-direction: column; height: 100%; }
.v-tabs-items { background-color: white; }

/* Responsive Adjustments */
@media (max-width: 959px) {
  .fill-height {
    height: auto !important;
  }
  .v-row.fill-height {
    flex-direction: column;
  }
  .chat-area {
    height: 65vh; /* Assign a substantial height for chat on mobile */
  }
  .info-sidebar {
    height: auto; /* Let the sidebar take its own height */
    border-left: none;
    border-top: 1px solid #e0e0e0;
  }
}
</style>
