<template>
  <div>
    <v-container fluid>
      <v-row class="fill-height">
        <!-- Left Column -->
        <v-col cols="12" md="3" class="pa-4">
          <v-card class="profile-card pa-4 text-center" elevation="1">
            <v-avatar size="90" class="mb-4 elevation-2">
              <v-img :src="userAvatar" />
            </v-avatar>
            <h2 class="text-h6 font-weight-bold">{{ userName }}</h2>
            <p class="body-2 mb-4">{{ userEmail }}</p>
            <v-divider class="my-2"></v-divider>
            <div class="text-left body-2 my-4">
              <p><v-icon small left>mdi-gender-male-female</v-icon><strong>เพศ:</strong> {{ userGender }}</p>
              <p class="mb-0"><v-icon small left>mdi-heart-outline</v-icon><strong>ความสนใจ:</strong> {{ userInterests }}</p>
            </div>
            <v-btn block color="primary" outlined @click="editProfile">
              <v-icon left>mdi-pencil-outline</v-icon> แก้ไขข้อมูล
            </v-btn>
          </v-card>

          <v-card class="mt-4" elevation="1">
            <v-tabs v-model="friendTab" background-color="transparent" grow>
              <v-tab>แนะนำ</v-tab>
              <v-tab>
                คำขอ
                <v-badge v-if="friendRequests.length > 0" color="pink" :content="friendRequests.length" inline></v-badge>
              </v-tab>
            </v-tabs>
            <v-tabs-items v-model="friendTab" class="pa-2">
              <v-tab-item>
                <v-list dense v-if="friendSuggestions.length > 0">
                  <v-list-item v-for="user in friendSuggestions" :key="user.user_id">
                    <v-list-item-avatar size="36">
                      <v-img :src="user.avatar_url"></v-img>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-medium">{{ user.username }}</v-list-item-title>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon small color="green lighten-1" @click="handleFriendAction(user.user_id, 'add')"><v-icon small>mdi-account-plus-outline</v-icon></v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
                 <div v-else class="text-center pa-4">ไม่มีเพื่อนแนะนำ</div>
              </v-tab-item>
              <v-tab-item>
                <v-list dense v-if="friendRequests.length > 0">
                  <v-list-item v-for="user in friendRequests" :key="user.user_id">
                    <v-list-item-avatar size="36">
                      <v-img :src="user.avatar_url"></v-img>
                    </v-list-item-avatar>
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-medium">{{ user.username }}</v-list-item-title>
                    </v-list-item-content>
                    <v-list-item-action class="d-flex flex-row">
                      <v-btn icon small color="green lighten-1" @click="handleFriendAction(user.user_id, 'accept')"><v-icon small>mdi-check</v-icon></v-btn>
                      <v-btn icon small color="red lighten-1" @click="handleFriendAction(user.user_id, 'reject')"><v-icon small>mdi-close</v-icon></v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
                <div v-else class="text-center pa-4">ไม่มีคำขอเป็นเพื่อน</div>
              </v-tab-item>
            </v-tabs-items>
          </v-card>
        </v-col>

        <!-- Middle Column -->
        <v-col cols="12" md="6" class="pa-4">
          <v-card class="create-post-card mb-6" elevation="1">
            <v-card-text class="pa-4">
              <div class="d-flex align-start">
                <v-avatar size="40" class="mr-4">
                  <v-img :src="userAvatar" />
                </v-avatar>
                <v-textarea v-model="newPostContent" placeholder="คุณกำลังคิดอะไรอยู่..." rows="2" auto-grow hide-details solo flat></v-textarea>
              </div>
              <div v-if="newPostImageUrl" class="mt-4 text-center">
                  <v-img :src="newPostImageUrl" class="rounded-lg" contain max-height="250"></v-img>
              </div>
            </v-card-text>
            <v-card-actions class="pa-4 pt-0">
                <v-btn text @click="$refs.fileInput.click()">
                    <v-icon left color="green">mdi-image-area</v-icon> รูปภาพ
                </v-btn>
                <v-file-input ref="fileInput" v-model="newPostImageFile" accept="image/*" hide-input @change="onNewPostImageSelected"></v-file-input>
                <v-btn v-if="newPostImageUrl" text small color="red" @click="removeNewPostImage">
                    <v-icon left>mdi-close-circle-outline</v-icon> ลบรูป
                </v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" elevation="0" :disabled="!newPostContent.trim() && !newPostImageFile" :loading="isCreatingPost" @click="createPost">โพสต์</v-btn>
            </v-card-actions>
          </v-card>

          <div v-if="loadingPosts" class="text-center py-16">
            <v-progress-circular size="64" indeterminate color="primary"></v-progress-circular>
          </div>
          <div v-else>
            <post-card v-for="post in posts" :key="post.post_id" :post="post" @post-updated="fetchPosts" @comment-added="fetchPosts" @comment-deleted="fetchPosts" @post-deleted="fetchPosts" class="mb-4" elevation="1" />
            <div v-if="posts.length === 0" class="text-center mt-16">
                <v-icon large color="grey lighten-1">mdi-text-box-outline</v-icon>
                <p class="mt-4">ยังไม่มีโพสต์ในฟีดของคุณ</p>
            </div>
          </div>
        </v-col>

        <!-- Right Column -->
        <v-col cols="12" md="3" class="pa-4">
          <v-card class="online-friends-card" elevation="1">
            <v-card-title class="text-h6 font-weight-bold">เพื่อนออนไลน์ ({{ friends.length }})</v-card-title>
            <v-divider></v-divider>
            <v-list v-if="friends.length > 0">
              <v-list-item v-for="friend in friends" :key="friend.user_id">
                <v-list-item-avatar size="40">
                  <v-img :src="friend.avatar_url || 'https://randomuser.me/api/portraits/lego/2.jpg'" />
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-medium">{{ friend.username }}</v-list-item-title>
                </v-list-item-content>
                <v-list-item-icon>
                    <v-icon color="green accent-4" small>mdi-circle</v-icon>
                </v-list-item-icon>
              </v-list-item>
            </v-list>
            <div v-else class="text-center pa-8">
              ไม่มีเพื่อนออนไลน์
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
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
        try {
            const res = await this.$axios.post('/friend_request.php', {
                user_id_1: this.currentUserId,
                user_id_2: targetUserId,
                action: action,
            });
            if (res.data.success) {
                alert(res.data.message);
                this.fetchAllFriendData();
            } else {
                 alert('เกิดข้อผิดพลาด: ' + res.data.error);
            }
        } catch (error) {
            console.error(`Error performing friend action (${action}):`, error);
            alert(`การเชื่อมต่อล้มเหลว`);
        }
    },
    async fetchPosts() {
        this.loadingPosts = true;
        try {
            const res = await this.$axios.get(`/get_posts.php?user_id=${this.currentUserId}`);
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
.profile-card, .create-post-card, .online-friends-card, .v-card {
  border-radius: 12px;
}

.v-list-item-avatar img {
  object-fit: cover;
  border-radius: 50%;
}

.create-post-card .v-textarea {
    border-radius: 20px;
}

.v-tabs-items {
    border-radius: 0 0 12px 12px;
}
</style>
