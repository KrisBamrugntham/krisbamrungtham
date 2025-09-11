<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card class="pa-4 pa-md-8">
      <div class="text-center">
        <h2 class="text-h5 font-weight-bold">สร้างบัญชีผู้ใช้ใหม่</h2>
        <p class="grey--text text--darken-1">เข้าร่วมชุมชนของเราวันนี้!</p>
      </div>
      <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submit">
        <v-text-field
          v-model="form.name"
          :rules="[v => !!v || 'กรุณากรอกชื่อ']"
          label="ชื่อผู้ใช้"
          prepend-inner-icon="mdi-account-outline"
          outlined
          required
          class="mb-2"
        />
        <v-text-field
          v-model="form.email"
          :rules="emailRules"
          label="อีเมล"
          prepend-inner-icon="mdi-email-outline"
          outlined
          required
          class="mb-2"
        />
        <v-text-field
          v-model="form.password"
          :append-icon="showPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
          :type="showPassword ? 'text' : 'password'"
          @click:append="showPassword = !showPassword"
          :rules="[v => !!v || 'กรุณากรอกรหัสผ่าน', v => v.length >= 6 || 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']"
          label="รหัสผ่าน"
          prepend-inner-icon="mdi-lock-outline"
          outlined
          required
          class="mb-2"
        />
        <v-text-field
          v-model="form.confirmPassword"
          :append-icon="showConfirmPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
          :type="showConfirmPassword ? 'text' : 'password'"
          @click:append="showConfirmPassword = !showConfirmPassword"
          :rules="[v => !!v || 'กรุณายืนยันรหัสผ่าน', v => v === form.password || 'รหัสผ่านไม่ตรงกัน']"
          label="ยืนยันรหัสผ่าน"
          prepend-inner-icon="mdi-lock-check-outline"
          outlined
          required
          class="mb-2"
        />
        <v-select
          v-model="form.interests"
          :items="interestOptions"
          label="ความสนใจ"
          prepend-inner-icon="mdi-heart-outline"
          multiple
          chips
          outlined
          :rules="[v => v.length > 0 || 'กรุณาเลือกความสนใจอย่างน้อย 1 รายการ']"
          required
          class="mb-2"
        />
        <div>
            <p class="grey--text text--darken-1 mb-2">เพศ</p>
            <v-radio-group v-model="form.gender" :rules="[v => !!v || 'กรุณาเลือกเพศ']" row class="mt-0">
                <v-radio label="ชาย" value="ชาย" />
                <v-radio label="หญิง" value="หญิง" />
                <v-radio label="อื่น ๆ" value="อื่น ๆ" />
            </v-radio-group>
        </div>
        
        <v-alert v-if="error" type="error" dense text class="mb-4">{{ error }}</v-alert>
        <v-alert v-if="success" type="success" dense text class="mb-4">ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบ</v-alert>

        <v-btn color="primary" class="mt-2" large block type="submit" :loading="loading" :disabled="!valid || loading">
          ลงทะเบียน
        </v-btn>

        <div class="text-center mt-6">
          <span>มีบัญชีอยู่แล้ว? </span>
          <a href="#" class="font-weight-bold" @click.prevent="$emit('switch-to-login')">
            เข้าสู่ระบบที่นี่
          </a>
        </div>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'RegisterForm',
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      dialog: this.value,
      valid: false,
      loading: false,
      success: false,
      error: null,
      showPassword: false,
      showConfirmPassword: false,
      form: {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        gender: '',
        interests: []
      },
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
      emailRules: [
        v => !!v || 'กรุณากรอกอีเมล',
        v => /.+@.+\..+/.test(v) || 'รูปแบบอีเมลไม่ถูกต้อง'
      ]
    }
  },
  watch: {
    value(val) {
      this.dialog = val;
    },
    dialog(val) {
      this.$emit('input', val);
      if (!val) {
        this.resetForm();
      }
    }
  },
  methods: {
    async submit() {
      this.success = false;
      this.error = null;
      if (this.$refs.form.validate()) {
        this.loading = true;
        const payload = {
          username: this.form.name,
          email: this.form.email,
          password: this.form.password,
          gender: this.form.gender,
          interests: this.form.interests.join(','),
          avatar_url: 'default.png'
        };
        try {
          const res = await this.$axios.post('/insert.php', payload);
          const data = res.data;
          
          if (data.success) {
            this.success = true;
            setTimeout(() => {
              this.dialog = false;
              this.$emit('switch-to-login');
            }, 1500);
          } else {
            this.error = data.error || 'เกิดข้อผิดพลาดในการลงทะเบียน';
          }
        } catch (err) {
            this.error = 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้';
            console.error(err);
        } finally {
            this.loading = false;
        }
      }
    },
    resetForm() {
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
      this.valid = false;
      this.success = false;
      this.loading = false;
      this.form = {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        gender: '',
        interests: []
      };
      this.error = null;
    }
  }
}
</script>

<style scoped>
.v-card {
    border-radius: 16px;
}
</style>
