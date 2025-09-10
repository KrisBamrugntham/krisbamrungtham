<template>
    <v-card class="d-flex flex-column fill-height" elevation="2">
        <v-card-title class="grey lighten-4 py-2">
            <v-btn icon to="/groups"><v-icon>mdi-arrow-left</v-icon></v-btn>
            <span class="font-weight-bold ml-2">Group Chat</span>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text ref="messageContainer" class="flex-grow-1 overflow-y-auto pa-4">
             <div v-for="msg in messages" :key="msg.message_id" class="d-flex my-2 align-end" :class="{'justify-end': msg.user_id == currentUserId}">
                <v-avatar v-if="msg.user_id != currentUserId" size="32" class="mr-2">
                    <v-img :src="msg.avatar_url || 'https://randomuser.me/api/portraits/lego/1.jpg'"></v-img>
                </v-avatar>
                <div class="d-flex flex-column" :class="{'align-end': msg.user_id == currentUserId}">
                    <div v-if="msg.user_id != currentUserId" class="caption grey--text px-3">{{ msg.username }}</div>
                    <div class="message-bubble" :class="{'primary white--text': msg.user_id == currentUserId, 'grey lighten-3': msg.user_id != currentUserId}">
                        {{ msg.message }}
                    </div>
                </div>
            </div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-2 grey lighten-4">
            <v-text-field v-model="newMessage" placeholder="พิมพ์ข้อความ..." hide-details outlined dense @keydown.enter.prevent="sendMessage"></v-text-field>
            <v-btn class="ml-2" color="primary" icon @click="sendMessage" :disabled="!newMessage.trim()"><v-icon>mdi-send</v-icon></v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'GroupChatPage',
    data() {
        return {
            messages: [],
            newMessage: '',
            currentUserId: null,
            groupId: this.$route.params.id,
            polling: null
        }
    },
    created() {
        if (process.client) {
            this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
        }
        this.startChat();
    },
    beforeDestroy() {
        clearInterval(this.polling);
    },
    methods: {
        startChat() {
            this.fetchMessages();
            this.polling = setInterval(this.fetchMessages, 3000);
        },
        async fetchMessages() {
            try {
                const res = await this.$axios.get(`/get_group_messages.php?group_id=${this.groupId}`);
                if (res.data.status === 'success' && JSON.stringify(this.messages) !== JSON.stringify(res.data.data)) {
                    this.messages = res.data.data;
                    this.scrollToBottom();
                }
            } catch (error) {
                console.error("Failed to fetch group messages", error);
            }
        },
        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const tempMessage = this.newMessage;
            this.newMessage = '';
            try {
                await this.$axios.post('/send_group_message.php', {
                    group_id: this.groupId,
                    sender_id: this.currentUserId,
                    message: tempMessage
                });
                await this.fetchMessages();
            } catch (error) {
                console.error("Failed to send group message", error);
                this.newMessage = tempMessage; // Restore on failure
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messageContainer;
                if (container) container.scrollTop = container.scrollHeight;
            });
        }
    }
}
</script>

<style scoped>
.fill-height {
    height: calc(100vh - 88px); /* Adjust based on your header/footer height */
}
.message-bubble {
  padding: 8px 12px;
  border-radius: 18px;
  max-width: 100%;
  word-wrap: break-word;
}
</style>