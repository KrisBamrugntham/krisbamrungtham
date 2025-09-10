<template>
  <div class="d-flex flex-column fill-height chat-window-container">
    <!-- Chat Header -->
    <v-toolbar v-if="friend" flat class="chat-header">
      <v-list-item two-line class="pa-0">
        <v-list-item-avatar class="mr-3">
          <v-img :src="friend.avatar_url || defaultAvatar"></v-img>
        </v-list-item-avatar>
        <v-list-item-content>
          <v-list-item-title class="font-weight-bold">{{ friend.username }}</v-list-item-title>
          <v-list-item-subtitle>ออนไลน์</v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
      <v-spacer></v-spacer>
      <v-btn icon>
        <v-icon>mdi-video-outline</v-icon>
      </v-btn>
      <v-btn icon>
        <v-icon>mdi-dots-vertical</v-icon>
      </v-btn>
    </v-toolbar>

    <!-- Welcome Message -->
    <div v-if="!friend" class="d-flex fill-height align-center justify-center text-center welcome-message">
        <div>
            <v-icon size="100" color="grey lighten-2">mdi-wechat</v-icon>
            <h2 class="mt-4 font-weight-light grey--text text--darken-1">Edukris Chat</h2>
            <p class="grey--text">เลือกเพื่อนเพื่อเริ่มการสนทนา</p>
        </div>
    </div>

    <!-- Messages Area -->
    <v-card-text v-else ref="messageContainer" class="flex-grow-1 overflow-y-auto pa-4 chat-area">
      <div v-for="msg in messages" :key="msg.chat_id" class="message-row d-flex my-1" :class="{ 'justify-end': msg.sender_id == currentUserId }">
        <div class="message-bubble" :class="{ 'sent': msg.sender_id == currentUserId, 'received': msg.sender_id != currentUserId }">
          <div class="message-content">{{ msg.message }}</div>
          <div class="message-time">{{ formatTime(msg.created_at) }}</div>
        </div>
      </div>
    </v-card-text>

    <!-- Message Input -->
    <div v-if="friend" class="chat-footer pa-2">
      <v-btn icon class="mr-2">
        <v-icon>mdi-paperclip</v-icon>
      </v-btn>
      <v-text-field
        v-model="newMessage"
        placeholder="พิมพ์ข้อความ..."
        hide-details
        filled
        rounded
        dense
        class="flex-grow-1"
        @keydown.enter.prevent="sendMessage"
      ></v-text-field>
      <v-btn icon class="ml-2" color="primary" @click="sendMessage" :disabled="!newMessage.trim()">
        <v-icon>mdi-send</v-icon>
      </v-btn>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    friend: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      currentUserId: null,
      polling: null,
      defaultAvatar: 'https://randomuser.me/api/portraits/men/85.jpg',
    };
  },
  watch: {
    friend(newFriend, oldFriend) {
      if (newFriend && (!oldFriend || newFriend.user_id !== oldFriend.user_id)) {
        this.startChat();
      }
      if (!newFriend) {
        this.stopChat();
      }
    },
  },
  created() {
    if (process.client) {
      this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
    }
  },
  beforeDestroy() {
    this.stopChat();
  },
  methods: {
    formatTime(dateString) {
        if (!dateString) return '';
        const options = { hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleTimeString('th-TH', options);
    },
    startChat() {
      this.messages = [];
      this.fetchMessages();
      this.stopChat();
      this.polling = setInterval(this.fetchMessages, 2500);
    },
    stopChat() {
      clearInterval(this.polling);
    },
    async fetchMessages() {
      if (!this.friend) return;
      try {
        const res = await this.$axios.get(`/get_messages.php?user_id=${this.currentUserId}&friend_id=${this.friend.user_id}`);
        if (res.data.status === 'success') {
          if (JSON.stringify(this.messages) !== JSON.stringify(res.data.data)) {
            this.messages = res.data.data;
            this.scrollToBottom();
          }
        }
      } catch (error) {
        console.error("Failed to fetch messages", error);
        this.stopChat();
      }
    },
    async sendMessage() {
      if (!this.newMessage.trim() || !this.friend) return;
      const tempMessageContent = this.newMessage;
      this.newMessage = '';

      try {
        const res = await this.$axios.post('/send_message.php', {
          sender_id: this.currentUserId,
          receiver_id: this.friend.user_id,
          message: tempMessageContent,
        });
        if (res.data.success) {
          await this.fetchMessages(); // Fetch all messages to get the new one from server
        } else {
          throw new Error(res.data.error);
        }
      } catch (error) {
        console.error("Failed to send message", error);
        alert("การเชื่อมต่อล้มเหลว ไม่สามารถส่งข้อความได้");
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messageContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
  },
};
</script>

<style scoped>
.chat-window-container {
  background-color: #F0F2F5;
}

.chat-header {
  border-bottom: 1px solid #e0e0e0 !important;
  background-color: #F5F5F5;
}

.chat-area {
  background-color: #EFEAE2; /* WhatsApp-like background */
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARMAAAARMAAQMAAAA/3/8OAAAABlBMVEXd3d3d3d3d3d0m875OAAAAAXRSTlMAQObYZgAAAB9JREFUeN7twQENAAAAwiD7p7bHBwwAAAAg7AEnYgAB9p0s9wAAAABJRU5ErkJggg==');
}

.message-row {
  max-width: 75%;
}

.message-bubble {
  padding: 6px 12px;
  border-radius: 12px;
  position: relative;
  word-wrap: break-word;
  box-shadow: 0 1px 1px rgba(0,0,0,0.05);
}

.message-bubble.sent {
  background-color: #DCF8C6;
  border-top-right-radius: 0;
}

.message-bubble.received {
  background-color: #FFFFFF;
  border-top-left-radius: 0;
}

.message-content {
    padding-bottom: 16px; /* Space for time */
}

.message-time {
    font-size: 0.7rem;
    color: grey;
    position: absolute;
    bottom: 4px;
    right: 8px;
}

.chat-footer {
  background-color: #F0F2F5;
  border-top: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
}

.welcome-message {
    background-color: #F0F2F5;
}

.v-text-field--filled.v-input--dense.v-text-field--single-line, .v-text-field--filled.v-input--dense.v-text-field--multi-line {
    border-radius: 20px;
}
</style>
