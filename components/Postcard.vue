<template>
  <v-card class="post-card" elevation="1">
    <!-- Post Header -->
    <v-card-title class="pa-4">
      <v-avatar size="40" class="mr-3">
        <v-img :src="post.avatar_url || 'https://randomuser.me/api/portraits/lego/1.jpg'"></v-img>
      </v-avatar>
      <div>
        <div class="font-weight-bold">{{ post.username }}</div>
        <div class="caption grey--text text--darken-1">{{ formatDate(post.created_at) }}</div>
      </div>
      <v-spacer></v-spacer>
      <v-menu v-if="isOwner" offset-y>
        <template v-slot:activator="{ on }">
          <v-btn icon v-on="on"><v-icon>mdi-dots-vertical</v-icon></v-btn>
        </template>
        <v-list dense>
          <v-list-item @click="editPost">
            <v-list-item-icon class="mr-3"><v-icon small>mdi-pencil-outline</v-icon></v-list-item-icon>
            <v-list-item-title>แก้ไขโพสต์</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-card-title>

    <!-- Post Content -->
    <div v-if="isEditing" class="pa-4 pt-0">
      <v-textarea v-model="editedContent" outlined rows="3" auto-grow label="แก้ไขเนื้อหาโพสต์"></v-textarea>
      <v-img v-if="editedImageUrl" :src="editedImageUrl" class="mb-3 rounded-lg" contain max-height="300"></v-img>
      <v-file-input v-model="editedImageFile" label="เปลี่ยนรูปภาพ" accept="image/*" prepend-icon="mdi-camera" show-size dense outlined class="mb-3" @change="onEditedImageSelected"></v-file-input>
      <v-btn v-if="editedImageUrl && !editedImageFile" small text color="error" @click="removeEditedImage">
        <v-icon left>mdi-close-circle</v-icon> ลบรูปภาพ
      </v-btn>
      <v-card-actions class="pa-0">
        <v-spacer></v-spacer>
        <v-btn text @click="cancelEdit">ยกเลิก</v-btn>
        <v-btn color="primary" :loading="isSavingEdit" @click="saveEdit">บันทึก</v-btn>
      </v-card-actions>
    </div>
    <div v-else>
      <v-card-text class="post-content body-1 py-0 px-4" v-if="post.content">
        {{ post.content }}
      </v-card-text>
      <v-img v-if="post.image_url" :src="post.image_url" class="post-image mt-2" max-height="500"></v-img>
    </div>

    <!-- Action Bar -->
    <v-card-actions class="pa-2">
      <v-btn text>
        <v-icon left>mdi-thumb-up-outline</v-icon>
        ถูกใจ
      </v-btn>
      <v-btn text>
        <v-icon left>mdi-comment-outline</v-icon>
        ความคิดเห็น
      </v-btn>
      <v-spacer></v-spacer>
    </v-card-actions>

    <v-divider></v-divider>

    <!-- Comments Section -->
    <div class="pa-4">
      <div v-for="comment in post.comments" :key="comment.comment_id" class="d-flex mb-3 align-start">
        <v-avatar size="32" class="mr-3 flex-shrink-0">
          <v-img :src="comment.avatar_url || 'https://randomuser.me/api/portraits/lego/2.jpg'"></v-img>
        </v-avatar>
        <div class="comment-bubble flex-grow-1">
          <div class="font-weight-bold body-2">{{ comment.username }}</div>
          <div class="body-2" style="white-space: pre-wrap;">{{ comment.content }}</div>
        </div>
        <v-btn v-if="isOwner" icon small class="ml-2" @click="deleteComment(comment.comment_id)">
          <v-icon small>mdi-delete-outline</v-icon>
        </v-btn>
      </div>
    </div>

    <!-- Comment Input -->
    <v-divider></v-divider>
    <div class="pa-4 d-flex align-center">
        <v-avatar size="36" class="mr-3">
            <v-img :src="userAvatar"></v-img>
        </v-avatar>
        <v-text-field
            v-model="newComment"
            placeholder="เขียนความคิดเห็น..."
            hide-details
            filled
            rounded
            dense
            @keydown.enter.prevent="postComment"
        ></v-text-field>
        <v-btn icon class="ml-2" color="primary" @click="postComment" :disabled="!newComment.trim()">
            <v-icon>mdi-send</v-icon>
        </v-btn>
    </div>
  </v-card>
</template>

<script>
export default {
  name: 'PostCard',
  props: {
    post: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isEditing: false,
      editedContent: this.post.content,
      editedImageUrl: this.post.image_url,
      editedImageFile: null,
      isSavingEdit: false,
      newComment: '',
      currentUserId: null,
      userAvatar: '',
    };
  },
  computed: {
    isOwner() {
      return this.currentUserId === parseInt(this.post.user_id);
    },
  },
  created() {
    if (process.client) {
      this.currentUserId = parseInt(localStorage.getItem('edukris_id'));
      this.userAvatar = localStorage.getItem('edukris_avatar') || 'https://randomuser.me/api/portraits/men/85.jpg';
    }
  },
  methods: {
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(dateString).toLocaleDateString('th-TH', options);
    },
    editPost() {
      this.isEditing = true;
      this.editedContent = this.post.content;
      this.editedImageUrl = this.post.image_url;
      this.editedImageFile = null;
    },
    cancelEdit() {
      this.isEditing = false;
    },
    onEditedImageSelected(file) {
      this.editedImageFile = file;
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => this.editedImageUrl = e.target.result;
        reader.readAsDataURL(file);
      } else {
        this.editedImageUrl = this.post.image_url;
      }
    },
    removeEditedImage() {
      this.editedImageUrl = null;
      this.editedImageFile = null;
    },
    async saveEdit() {
      if (!this.editedContent.trim() && !this.editedImageUrl && !this.editedImageFile) {
        alert('โปรดใส่เนื้อหาหรือเลือกรูปภาพ');
        return;
      }
      this.isSavingEdit = true;
      let finalImageUrl = this.editedImageUrl;
      try {
        if (this.editedImageFile) {
          const formData = new FormData();
          formData.append('image', this.editedImageFile);
          const uploadRes = await this.$axios.post('/upload_image.php', formData);
          if (uploadRes.data.success) {
            finalImageUrl = uploadRes.data.image_url;
          } else {
            throw new Error(uploadRes.data.error);
          }
        }
        const payload = {
          post_id: this.post.post_id,
          content: this.editedContent,
          image_url: finalImageUrl,
          user_id: this.currentUserId
        };
        const res = await this.$axios.post('/update_post.php', payload);
        if (res.data.success) {
          this.$emit('post-updated');
          this.isEditing = false;
        } else {
          throw new Error(res.data.error);
        }
      } catch (error) {
        console.error('Failed to update post', error);
        alert('เกิดข้อผิดพลาดในการอัปเดตโพสต์: ' + error.message);
      } finally {
        this.isSavingEdit = false;
      }
    },
    async postComment() {
      if (!this.newComment.trim()) return;
      try {
        const res = await this.$axios.post('/create_comment.php', {
          post_id: this.post.post_id,
          user_id: this.currentUserId,
          content: this.newComment,
        });
        if (res.data.success) {
          this.$emit('comment-added');
          this.newComment = '';
        } else {
          alert('เกิดข้อผิดพลาด: ' + res.data.error);
        }
      } catch (error) {
        console.error('Failed to post comment', error);
        alert('การเชื่อมต่อล้มเหลว ไม่สามารถแสดงความคิดเห็นได้');
      }
    },
    async deleteComment(commentId) {
      if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบคอมเมนต์นี้?')) {
        try {
          const res = await this.$axios.post('/delete_comment.php', {
            comment_id: commentId,
            user_id: this.currentUserId
          });
          if (res.data.success) {
            this.$emit('comment-deleted');
          } else {
            alert('ไม่สามารถลบคอมเมนต์ได้: ' + res.data.error);
          }
        } catch (error) {
          console.error('Failed to delete comment', error);
        }
      }
    }
  },
};
</script>

<style scoped>
.post-card {
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.post-content {
  white-space: pre-wrap;
  line-height: 1.6;
}

.post-image {
  max-width: 100%;
  object-fit: cover;
}

.comment-bubble {
  background-color: #f0f2f5;
  border-radius: 18px;
  padding: 8px 12px;
}

.v-text-field.v-text-field--rounded > .v-input__control > .v-input__slot {
    padding: 0 16px;
}
</style>
