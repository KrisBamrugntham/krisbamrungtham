<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card class="pa-4 pa-md-8">
      <div class="text-center">
        <h2 class="text-h5 font-weight-bold">ยินดีต้อนรับกลับมา!</h2>
        <p class="grey--text text--darken-1">ลงชื่อเข้าใช้เพื่อดำเนินการต่อ</p>
      </div>
      <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submit">
        <v-text-field
          v-model="form.email"
          :rules="emailRules"
          label="อีเมล"
          prepend-inner-icon="mdi-email-outline"
          outlined
          required
          autocomplete="email"
          class="mb-2"
        />
        <v-text-field
          v-model="form.password"
          :append-icon="showPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
          :type="showPassword ? 'text' : 'password'"
          @click:append="showPassword = !showPassword"
          :rules="[v => !!v || 'กรุณากรอกรหัสผ่าน']"
          label="รหัสผ่าน"
          prepend-inner-icon="mdi-lock-outline"
          outlined
          required
          autocomplete="current-password"
        />
        
        <v-alert v-if="error" type="error" dense text class="mt-2 mb-4">{{ error }}</v-alert>

        <v-btn
          color="primary"
          class="mt-2"
          large
          block
          type="submit"
          :loading="loading"
          :disabled="!valid || loading"
        >
          เข้าสู่ระบบ
        </v-btn>

        <div class="text-center mt-6">
          <span>ยังไม่มีบัญชีใช่ไหม? </span>
          <a href="#" class="font-weight-bold" @click.prevent="$emit('switch-to-register')">
            สมัครสมาชิกที่นี่
          </a>
        </div>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'LoginForm',
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
      error: null,
      showPassword: false,
      form: {
        email: '',
        password: '',
      },
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
      this.error = null;
      if (this.$refs.form.validate()) {
        this.loading = true;
        try {
          const res = await this.$axios.post('/login.php', this.form);
          const data = res.data;

          if (data.success && data.user) {
            const user = data.user;
            
            localStorage.setItem('edukris_id', user.user_id);
            localStorage.setItem('edukris_name', user.username);
            localStorage.setItem('edukris_email', user.email);
            localStorage.setItem('edukris_gender', user.gender);
            localStorage.setItem('edukris_interests', user.interest);
            localStorage.setItem('edukris_avatar', user.avatar_url);
            if (user.role) {
              localStorage.setItem('edukris_role', user.role);
            }
            
            window.dispatchEvent(new Event('storage'));
            this.dialog = false;

            if (user.role === 'admin') {
              this.$router.push('/index-admin');
            } else {
              this.$router.push('/index-login');
            }

          } else {
            this.error = data.error || 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
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
      this.form = { email: '', password: '' };
      this.error = null;
      this.loading = false;
    }
  }
}
</script>

<style scoped>
.v-card {
    border-radius: 16px;
}
</style>
