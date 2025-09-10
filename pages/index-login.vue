<template>
  <div>
    <v-row no-gutters class="fill-height" style="min-height: calc(100vh - 64px);">
      <v-col cols="12" md="3" class="pa-0 grey lighten-4" style="min-width:260px;max-width:320px;">
        <v-list dense class="pt-10">
          <v-list-item>
            <v-list-item-avatar size="56">
              <v-img :src="userAvatar" />
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title class="font-weight-bold" style="font-size:1.1rem;">{{ userName }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-card class="mx-4 my-4 pa-4" outlined>
          <div class="mb-2"><strong>ชื่อผู้ใช้:</strong> {{ userName }}</div>
          <div class="mb-2"><strong>อีเมล:</strong> {{ userEmail }}</div>
          <div class="mb-2"><strong>เพศ:</strong> {{ userGender }}</div>
          <div><strong>ความสนใจ:</strong> {{ userInterests }}</div>
          <v-card-actions>
            <v-spacer />
            <v-btn small color="primary" @click="editProfile">แก้ไขข้อมูล</v-btn>
          </v-card-actions>
        </v-card>

        <v-card class="mx-4 my-4" outlined>
            <v-tabs v-model="friendTab" grow>
                <v-tab>แนะนำ</v-tab>
                <v-tab>คำขอ ({{ friendRequests.length }})</v-tab>
            </v-tabs>
            <v-tabs-items v-model="friendTab">
                <v-tab-item>
                    <v-list dense>
                        <v-list-item v-for="user in friendSuggestions" :key="user.user_id">
                            <v-list-item-avatar size="36">
                                <v-img :src="user.avatar_url"></v-img>
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title>{{ user.username }}</v-list-item-title>
                            </v-list-item-content>
                            <v-list-item-action>
                                <v-btn icon small color="green" @click="handleFriendAction(user.user_id, 'add')"><v-icon>mdi-account-plus</v-icon></v-btn>
                            </v-list-item-action>
                        </v-list-item>
                        <v-list-item v-if="friendSuggestions.length === 0">
                            <v-list-item-content class="text-center grey--text">ไม่มีเพื่อนแนะนำในขณะนี้</v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-tab-item>
                <v-tab-item>
                    <v-list dense>
                         <v-list-item v-for="user in friendRequests" :key="user.user_id">
                            <v-list-item-avatar size="36">
                                <v-img :src="user.avatar_url"></v-img>
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title>{{ user.username }}</v-list-item-title>
                            </v-list-item-content>
                            <v-list-item-action>
                                <v-btn icon small color="green" @click="handleFriendAction(user.user_id, 'accept')"><v-icon>mdi-check</v-icon></v-btn>
                                <v-btn icon small color="red" @click="handleFriendAction(user.user_id, 'reject')"><v-icon>mdi-close</v-icon></v-btn>
                            </v-list-item-action>
                        </v-list-item>
                        <v-list-item v-if="friendRequests.length === 0">
                            <v-list-item-content class="text-center grey--text">ไม่มีคำขอเป็นเพื่อน</v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-tab-item>
            </v-tabs-items>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6" class="pa-6">
        <h2 class="text-h4 font-weight-bold mb-6">Edukris Feed</h2>
        <v-card class="mb-6" elevation="2">
            <v-card-text>
                <v-textarea v-model="newPostContent" label="คุณกำลังคิดอะไรอยู่..." rows="3" auto-grow hide-details></v-textarea>
                <v-img v-if="newPostImageUrl" :src="newPostImageUrl" class="my-3" contain max-height="200"></v-img>
                <v-file-input v-model="newPostImageFile" label="เพิ่มรูปภาพ (ไม่จำเป็น)" accept="image/*" prepend-icon="mdi-camera" show-size dense class="mt-3" @change="onNewPostImageSelected"></v-file-input>
                <v-btn v-if="newPostImageUrl" small text color="error" @click="removeNewPostImage"><v-icon left>mdi-close-circle</v-icon> ลบรูปภาพ</v-btn>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" :disabled="!newPostContent && !newPostImageFile" :loading="isCreatingPost" @click="createPost">โพสต์</v-btn>
            </v-card-actions>
        </v-card>

        <div v-if="loadingPosts" class="text-center">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
        </div>
        <div v-else>
            <post-card v-for="post in posts" :key="post.post_id" :post="post" @post-updated="fetchPosts" @comment-added="fetchPosts" @comment-deleted="fetchPosts" />
            <div v-if="posts.length === 0" class="text-center grey--text mt-10">ยังไม่มีโพสต์...</div>
        </div>
      </v-col>

      <v-col cols="12" md="3" class="pa-0 grey lighten-4" style="min-width:220px;max-width:320px;">
        <div class="pt-10 px-4">
          <h3 class="text-h6 font-weight-bold mb-2">เพื่อนออนไลน์ ({{ friends.length }})</h3>
          <v-list dense v-if="friends.length > 0">
            <v-list-item v-for="friend in friends" :key="friend.user_id">
              <v-list-item-avatar size="36">
                <v-img :src="friend.avatar_url || 'https://randomuser.me/api/portraits/lego/2.jpg'" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ friend.username }}</v-list-item-title>
              </v-list-item-content>
              <v-icon color="success" small>mdi-circle</v-icon>
            </v-list-item>
          </v-list>
          <div v-else class="pa-4 text-center grey--text">
            ไม่มีเพื่อนที่ออนไลน์
          </div>
        </div>
      </v-col>
    </v-row>
    <edit-profile-form v-model="editDialog" :user="currentUser" @profile-updated="onProfileUpdate" />
  </div>
</template>

<script>
import EditProfileForm from '~/components/EditProfileForm.vue';
import PostCard from '~/components/PostCard.vue';

export default {
  name: 'IndexLogin',
  components: { EditProfileForm, PostCard },
  data() {
    return {
      // User data
      currentUserId: null,
      userName: '',
      userAvatar: 'https://randomuser.me/api/portraits/men/85.jpg',
      userEmail: '',
      userGender: '',
      userInterests: '',
      editDialog: false,
      currentUser: {},
      // Friends data
      friendTab: 0,
      friends: [],
      friendSuggestions: [],
      friendRequests: [],
      // Post Feed data
      posts: [],
      newPostContent: '',
      newPostImageFile: null,
      newPostImageUrl: null,
      loadingPosts: true,
      isCreatingPost: false,
    }
  },
  mounted() {
    this.loadUserData();
    this.fetchAllFriendData();
    this.fetchPosts();
  },
  methods: {
    loadUserData() {
      this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
      this.userName = localStorage.getItem('edukris_name') || 'Guest';
      this.userEmail = localStorage.getItem('edukris_email') || '-';
      this.userGender = localStorage.getItem('edukris_gender') || '-';
      this.userInterests = localStorage.getItem('edukris_interests') || '-';
      this.userAvatar = localStorage.getItem('edukris_avatar') || 'https://randomuser.me/api/portraits/men/85.jpg'; 
      
      if (!this.currentUserId) {
        this.$router.push('/');
      }
    },
    onProfileUpdate(updatedUser){
        localStorage.setItem('edukris_name', updatedUser.username);
        localStorage.setItem('edukris_email', updatedUser.email);
        localStorage.setItem('edukris_gender', updatedUser.gender);
        localStorage.setItem('edukris_interests', updatedUser.interests);
        localStorage.setItem('edukris_avatar', updatedUser.avatar_url);
        this.loadUserData();
        window.dispatchEvent(new Event('storage'));
        alert('อัปเดตข้อมูลสำเร็จ!');
    },
    editProfile() {
      this.currentUser = {
        user_id: this.currentUserId,
        username: this.userName,
        email: this.userEmail,
        gender: this.userGender,
        interests: this.userInterests,
        avatar_url: this.userAvatar,
      };
      this.editDialog = true;
    },
    async fetchAllFriendData() {
        if (!this.currentUserId) return;
        try {
            const statusRes = await this.$axios.get(`/get_friend_status.php?user_id=${this.currentUserId}`);
            if (statusRes.data.status === 'success') {
                this.friends = statusRes.data.data.friends;
                this.friendRequests = statusRes.data.data.received_requests;
            }
            const suggestionsRes = await this.$axios.get(`/get_friend_suggestions.php?user_id=${this.currentUserId}`);
            if (suggestionsRes.data.status === 'success') {
                this.friendSuggestions = suggestionsRes.data.data;
            }
        } catch (error) {
            console.error('Error fetching friend data:', error);
        }
    },
    async handleFriendAction(targetUserId, action) {
        console.log(`Action: ${action}, From: ${this.currentUserId}, To: ${targetUserId}`);
        try {
            const res = await this.$axios.post('/friend_request.php', {
                user_id_1: this.currentUserId,
                user_id_2: targetUserId,
                action: action,
            });
            if (res.data.success) {
                alert(res.data.message); // แจ้งเตือนเมื่อสำเร็จ
                this.fetchAllFriendData();
            } else {
                 alert('เกิดข้อผิดพลาด: ' + res.data.error);
            }
        } catch (error) {
            console.error(`Error performing friend action (${action}):`, error);
            alert(`การเชื่อมต่อล้มเหลว ไม่สามารถ${action}เพื่อนได้`);
        }
    },
    async fetchPosts() {
        this.loadingPosts = true;
        try {
            const res = await this.$axios.get('/get_posts.php');
            if (res.data.status === 'success') {
                this.posts = res.data.data;
            }
        } catch (error) {
            console.error('Error fetching posts:', error);
        } finally {
            this.loadingPosts = false;
        }
    },
    onNewPostImageSelected(file) {
      this.newPostImageFile = file;
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => this.newPostImageUrl = e.target.result;
        reader.readAsDataURL(file);
      } else {
        this.newPostImageUrl = null;
      }
    },
    removeNewPostImage() {
        this.newPostImageUrl = null;
        this.newPostImageFile = null;
    },
    async createPost() {
        if ((!this.newPostContent.trim() && !this.newPostImageFile) || !this.currentUserId) {
            alert('โปรดใส่เนื้อหาหรือเลือกรูปภาพ');
            return;
        }
        this.isCreatingPost = true;
        let imageUrl = null;
        try {
            if (this.newPostImageFile) {
                const formData = new FormData();
                formData.append('image', this.newPostImageFile);
                const uploadRes = await this.$axios.post('/upload_image.php', formData);
                if (uploadRes.data.success) {
                    imageUrl = uploadRes.data.image_url;
                } else {
                    throw new Error(uploadRes.data.error);
                }
            }
            const postPayload = {
                user_id: this.currentUserId,
                content: this.newPostContent,
                image_url: imageUrl,
            };
            const res = await this.$axios.post('/create_post.php', postPayload);
            if (res.data.success) {
                this.newPostContent = '';
                this.newPostImageFile = null;
                this.newPostImageUrl = null;
                await this.fetchPosts();
            } else {
                throw new Error(res.data.error);
            }
        } catch (error) {
            console.error('Error creating post:', error);
            alert('เกิดข้อผิดพลาดในการโพสต์: ' + error.message);
        } finally {
            this.isCreatingPost = false;
        }
    }
  }
}
</script>

<style scoped>
.v-list-item-avatar img {
  object-fit: cover;
}
</style>