<template>
  <v-dialog v-model="dialog" max-width="600px">
    <v-card class="pa-4 pa-md-6" rounded="lg">
      <v-card-title class="justify-center">
        <span class="text-h5 font-weight-bold">สร้างกลุ่มใหม่ของคุณ</span>
      </v-card-title>
      <v-card-subtitle class="text-center mt-1">แบ่งปันและเรียนรู้ร่วมกับผู้อื่น</v-card-subtitle>

      <v-card-text class="mt-4">
        <v-form ref="form" v-model="valid">
          <v-text-field
            v-model="group.name"
            label="ชื่อกลุ่ม*"
            :rules="[v => !!v || 'กรุณาใส่ชื่อกลุ่ม']"
            prepend-inner-icon="mdi-account-group-outline"
            outlined
            required
            class="mb-3"
          ></v-text-field>
          <v-textarea
            v-model="group.description"
            label="คำอธิบายกลุ่ม"
            prepend-inner-icon="mdi-card-text-outline"
            rows="3"
            auto-grow
            outlined
            class="mb-3"
          ></v-textarea>
          
          <!-- Image Preview -->
          <div v-if="imagePreviewUrl" class="mb-4 text-center">
            <v-img :src="imagePreviewUrl" class="rounded-lg elevation-2" contain max-height="200"></v-img>
          </div>

          <v-file-input
            v-model="imageFile"
            label="เลือกรูปภาพกลุ่ม (ไม่จำเป็น)"
            accept="image/*"
            prepend-icon="mdi-camera"
            outlined
            show-size
            @change="onImageSelected"
          ></v-file-input>

        </v-form>
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-btn text large @click="close">ยกเลิก</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" large depressed :disabled="!valid" :loading="loading" @click="createGroup">สร้างกลุ่ม</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {
    value: Boolean,
  },
  data() {
    return {
      valid: false,
      loading: false,
      group: {
        name: '',
        description: '',
      },
      imageFile: null,
      imagePreviewUrl: null,
    };
  },
  computed: {
    dialog: {
      get() {
        return this.value;
      },
      set(val) {
        this.$emit('input', val);
      },
    },
  },
  methods: {
    close() {
      this.$refs.form.reset();
      this.group = { name: '', description: '' };
      this.imageFile = null;
      this.imagePreviewUrl = null;
      this.dialog = false;
    },
    onImageSelected(file) {
      this.imageFile = file;
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreviewUrl = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.imagePreviewUrl = null;
      }
    },
    async createGroup() {
      if (!this.$refs.form.validate()) return;
      
      this.loading = true;
      const userId = localStorage.getItem('edukris_id');
      let imageUrl = null;

      try {
        // 1. Upload image if selected
        if (this.imageFile) {
          const formData = new FormData();
          formData.append('image', this.imageFile);
          const uploadRes = await this.$axios.post('/upload_image.php', formData);
          if (uploadRes.data.success) {
            imageUrl = uploadRes.data.image_url;
          } else {
            throw new Error(uploadRes.data.error || 'Image upload failed');
          }
        }

        // 2. Create group with the new image URL (or null)
        const res = await this.$axios.post('/create_group.php', {
          group_name: this.group.name,
          description: this.group.description,
          image_url: imageUrl,
          created_by: userId,
        });

        if (res.data.success) {
          this.$emit('group-created');
          this.close();
        } else {
          alert('เกิดข้อผิดพลาด: ' + res.data.error);
        }
      } catch (error) {
        console.error('Failed to create group', error);
        alert('การเชื่อมต่อล้มเหลว ไม่สามารถสร้างกลุ่มได้: ' + error.message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
