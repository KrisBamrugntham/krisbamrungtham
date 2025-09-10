<template>
  <v-card class="d-flex flex-column fill-height" elevation="2">
    <v-card-title v-if="friend" class="grey lighten-4 py-2">
      <v-avatar size="40" class="mr-3">
        <v-img :src="friend.avatar_url || defaultAvatar"></v-img>
      </v-avatar>
      <span class="font-weight-bold">{{ friend.username }}</span>
    </v-card-title>
    
    <v-divider></v-divider>
    
    <v-card-text ref="messageContainer" class="flex-grow-1 overflow-y-auto pa-4">
      <div v-if="!friend" class="d-flex fill-height align-center justify-center grey--text">
          เลือกเพื่อนเพื่อเริ่มการสนทนา
      </div>
      <div v-else>
          <div v-for="msg in messages" :key="msg.chat_id" class="d-flex my-2 align-end" :class="{'justify-end': msg.sender_id == currentUserId}">
              <v-avatar v-if="msg.sender_id != currentUserId" size="32" class="mr-2">
                  <v-img :src="friend.avatar_url || defaultAvatar"></v-img>
              </v-avatar>
              
              <div class="message-bubble" :class="{'primary white--text': msg.sender_id == currentUserId, 'grey lighten-3': msg.sender_id != currentUserId}">
                  {{ msg.message }}
              </div>
          </div>
      </div>
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions v-if="friend" class="pa-2 grey lighten-4">
      <v-text-field
        v-model="newMessage"
        placeholder="พิมพ์ข้อความ..."
        hide-details
        outlined
        dense
        @keydown.enter.prevent="sendMessage"
      ></v-text-field>
      <v-btn class="ml-2" color="primary" icon @click="sendMessage" :disabled="!newMessage.trim()">
        <v-icon>mdi-send</v-icon>
      </v-btn>
    </v-card-actions>
  </v-card>
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
  // ... (ส่วน watch, created, beforeDestroy, methods เหมือนเดิมทุกประการ) ...
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

      const tempMessage = this.newMessage;
      this.newMessage = '';

      this.messages.push({
          chat_id: `temp-${Date.now()}`,
          sender_id: this.currentUserId,
          message: tempMessage,
      });
      this.scrollToBottom();

      try {
        const res = await this.$axios.post('/send_message.php', {
          sender_id: this.currentUserId,
          receiver_id: this.friend.user_id,
          message: tempMessage,
        });

        if (res.data.success) {
          await this.fetchMessages();
        } else {
            console.error('Server error on send:', res.data.error);
            this.messages.pop();
            this.newMessage = tempMessage;
            alert("ไม่สามารถส่งข้อความได้: " + res.data.error);
        }
      } catch (error) {
        console.error("Failed to send message", error);
        this.messages.pop();
        this.newMessage = tempMessage;
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
.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  max-width: 70%;
  word-wrap: break-word;
}
</style>