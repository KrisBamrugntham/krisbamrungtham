<template>
  <v-container fluid class="fill-height pa-0 chat-container">
    <v-row no-gutters class="fill-height">
      <!-- Conversations List -->
      <v-col cols="12" md="3" class="conversations-list-col">
        <v-toolbar flat class="px-2">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="ค้นหาเพื่อน"
            flat
            solo-inverted
            hide-details
            clearable
            clear-icon="mdi-close-circle-outline"
          ></v-text-field>
        </v-toolbar>
        <v-divider></v-divider>
        <v-list class="overflow-y-auto fill-height pa-0">
          <v-list-item-group v-model="selectedFriendIndex" color="primary">
            <template v-for="(friend, index) in filteredFriends">
              <v-list-item :key="friend.user_id" class="conversation-item">
                <v-list-item-avatar>
                  <v-img :src="friend.avatar_url"></v-img>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold">{{ friend.username }}</v-list-item-title>
                  <v-list-item-subtitle>คลิกเพื่อเริ่มแชท...</v-list-item-subtitle>
                </v-list-item-content>
                 <v-list-item-action>
                    <v-icon color="green accent-4" small>mdi-circle</v-icon>
                </v-list-item-action>
              </v-list-item>
              <v-divider :key="`divider-${index}`"></v-divider>
            </template>
          </v-list-item-group>
           <div v-if="filteredFriends.length === 0" class="text-center pa-4">
              ไม่พบรายชื่อเพื่อน
            </div>
        </v-list>
      </v-col>

      <!-- Chat Window -->
      <v-col cols="12" md="9" class="fill-height pa-0">
        <chat-window :friend="selectedFriend"></chat-window>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import ChatWindow from '~/components/ChatWindow.vue';

export default {
  name: 'ChatPage',
  components: { ChatWindow },
  data() {
    return {
      friends: [],
      selectedFriendIndex: null,
      currentUserId: null,
      search: '',
    }
  },
  computed: {
    selectedFriend() {
      if (this.selectedFriendIndex === null || !this.friends[this.selectedFriendIndex]) {
        return null;
      }
      return this.friends[this.selectedFriendIndex];
    },
    filteredFriends() {
        if (!this.search) {
            return this.friends;
        }
        return this.friends.filter(friend => 
            friend.username.toLowerCase().includes(this.search.toLowerCase())
        );
    }
  },
  watch: {
      // When search is used, the index might become invalid
      search() {
          this.selectedFriendIndex = null;
      }
  },
  mounted() {
    this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
    if (!this.currentUserId) {
      this.$router.push('/');
    }
    this.fetchFriends();
  },
  methods: {
    async fetchFriends() {
      try {
        const res = await this.$axios.get(`/get_friends.php?user_id=${this.currentUserId}`);
        if (res.data.status === 'success') {
          this.friends = res.data.data;
        } else {
            this.friends = [];
        }
      } catch (error) {
        console.error("Failed to fetch friends list", error);
      }
    }
  }
}
</script>

<style scoped>
.conversations-list-col {
  border-right: 1px solid #e0e0e0;
  display: flex;
  flex-direction: column;
  height: 100%;
}
.fill-height {
  height: calc(100vh - 64px); /* Adjust based on your main app-bar height */
}

.conversation-item .v-list-item__subtitle {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.v-list-item--active {
    background-color: #E8EAF6 !important; /* Indigo 50 */
}

.v-list-item-group .v-list-item--active .v-list-item__title {
    color: #3F51B5 !important; /* Indigo */
}

/* Responsive Adjustments */
@media (max-width: 959px) {
  .fill-height {
    height: auto !important;
  }
  .conversations-list-col {
    height: auto;
    max-height: 40vh; /* Limit height on mobile */
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
  }
}
</style>
