<template>
  <v-dialog v-model="dialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">Edit Profile</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-form ref="form" v-model="valid">
            <div class="d-flex justify-center mb-4">
                <v-avatar size="120">
                    <v-img :src="avatarPreview || form.avatar_url || 'https://randomuser.me/api/portraits/men/85.jpg'"></v-img>
                </v-avatar>
            </div>
            <v-file-input
                v-model="avatarFile"
                label="เปลี่ยนรูปโปรไฟล์"
                accept="image/*"
                prepend-icon="mdi-camera"
                show-size
                @change="onAvatarSelected"
            ></v-file-input>
            <v-divider class="my-4"></v-divider>
            <v-text-field v-model="form.username" label="Username*" :rules="[v => !!v || 'Username is required']" required />
            <v-text-field v-model="form.email" label="Email*" :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'E-mail must be valid']" required />
            <v-radio-group v-model="form.gender" label="Gender" row>
              <v-radio label="ชาย" value="ชาย" />
              <v-radio label="หญิง" value="หญิง" />
              <v-radio label="อื่น ๆ" value="อื่น ๆ" />
            </v-radio-group>
            <v-select
              v-model="form.interests"
              :items="interestOptions"
              label="Interests"
              multiple
              chips
            />
            </v-form>
        </v-container>
        <small>*indicates required field</small>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
        <v-btn color="blue darken-1" :disabled="!valid" :loading="isSaving" @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'EditProfileForm',
  props: {
    value: Boolean,
    user: {
      type: Object,
      default: () => ({})
    },
  },
  data() {
    return {
      valid: true,
      isSaving: false,
      form: {},
      avatarFile: null,
      avatarPreview: null,
       interestOptions: [
        'เทคโนโลยี',
        'วิทยาศาสตร์',
        'คณิตศาสตร์',
        'ภาษา',
        'ศิลปะ',
        'ธุรกิจ',
        'สุขภาพ',
        'อื่น ๆ'
      ],
    };
  },
  computed: {
    dialog: {
      get() { return this.value; },
      set(val) { this.$emit('input', val); },
    },
  },
  watch: {
    user(newUser) {
      if (newUser) {
        this.form = {
          ...newUser,
          interests: newUser.interests ? newUser.interests.split(',') : []
        };
        this.avatarFile = null;
        this.avatarPreview = null;
      } else {
        this.form = {};
      }
    },
  },
  methods: {
    close() {
      this.dialog = false;
    },
    onAvatarSelected(file) {
        this.avatarFile = file;
        if(file) {
            const reader = new FileReader();
            reader.onload = e => {
                this.avatarPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            this.avatarPreview = null;
        }
    },
    async save() {
      if (!this.$refs.form.validate()) return;
      this.isSaving = true;

      try {
        let newAvatarUrl = this.form.avatar_url;

        // 1. ถ้ามีการเลือกรูปใหม่ ให้อัปโหลดก่อน
        if (this.avatarFile) {
            const formData = new FormData();
            formData.append('avatar', this.avatarFile);
            formData.append('user_id', this.form.user_id);

            const uploadRes = await this.$axios.post('/update_avatar.php', formData);
            if (uploadRes.data.success) {
                newAvatarUrl = uploadRes.data.avatar_url;
            } else {
                throw new Error(uploadRes.data.error);
            }
        }
        
        // 2. อัปเดตข้อมูลอื่นๆ
        const profilePayload = {
            ...this.form,
            interest: this.form.interests.join(','),
        };
        delete profilePayload.interests;
        delete profilePayload.avatar_url; // ไม่ส่ง avatar_url ไปกับ update_profile

        const res = await this.$axios.post('/update_profile.php', profilePayload);
        
        if (res.data.success) {
            // ส่งข้อมูลทั้งหมดที่อัปเดตแล้วกลับไป
            this.$emit('profile-updated', {
                ...profilePayload,
                avatar_url: newAvatarUrl, // ส่ง URL รูปใหม่กลับไป
                interests: profilePayload.interest
            });
            this.close();
        } else {
            throw new Error(res.data.error);
        }

      } catch (error) {
          console.error('Failed to update profile', error);
          alert('เกิดข้อผิดพลาด: ' + error.message);
      } finally {
          this.isSaving = false;
      }
    },
  },
};
</script>