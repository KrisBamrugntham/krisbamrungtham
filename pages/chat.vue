<template>
    <v-container fluid class="fill-height pa-0">
        <v-row no-gutters class="fill-height">
            <v-col cols="12" md="3" class="grey lighten-5 fill-height" style="border-right: 1px solid #e0e0e0;">
                 <v-list class="overflow-y-auto fill-height pa-0">
                     <v-subheader>รายชื่อเพื่อน</v-subheader>
                     <v-list-item-group v-model="selectedFriendIndex" color="primary">
                        <v-list-item v-for="friend in friends" :key="friend.user_id">
                            <v-list-item-avatar>
                                <v-img :src="friend.avatar_url"></v-img>
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title>{{ friend.username }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                     </v-list-item-group>
                 </v-list>
            </v-col>
            <v-col cols="12" md="9" class="fill-height">
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
        }
    },
    computed: {
        selectedFriend() {
            if (this.selectedFriendIndex === null || !this.friends[this.selectedFriendIndex]) {
                return null;
            }
            return this.friends[this.selectedFriendIndex];
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
                }
            } catch (error) {
                console.error("Failed to fetch friends list", error);
            }
        }
    }
}
</script>

<style scoped>
.fill-height {
    height: calc(100vh - 88px); /* Adjust based on your header/footer height */
}
</style>