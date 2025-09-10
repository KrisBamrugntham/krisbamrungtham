<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card elevation="3" class="pa-6">
      <v-card-title class="headline font-weight-bold mb-2">ลงชื่อเข้าใช้ Edukris</v-card-title>
      <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submit">
        <v-text-field
          v-model="form.email"
          :rules="emailRules"
          label="อีเมล"
          required
          autocomplete="email"
        />
        <v-text-field
          v-model="form.password"
          :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :type="showPassword ? 'text' : 'password'"
          @click:append="showPassword = !showPassword"
          :rules="[v => !!v || 'กรุณากรอกรหัสผ่าน']"
          label="รหัสผ่าน"
          required
          autocomplete="current-password"
        />
        <v-btn
          color="primary"
          class="mt-4"
          large
          block
          type="submit"
          :disabled="!valid"
        >
          Sign In
        </v-btn>

        <div class="text-center mt-6">
          <span>ยังไม่มีบัญชี? </span>
          <a href="#" @click.prevent="$emit('switch-to-register')">
            Sign Up
          </a>
        </div>

        <v-alert
          v-if="error"
          type="error"
          class="mt-4"
          border="left"
          colored-border
          elevation="2"
        >
          {{ error }}
        </v-alert>
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
        try {
          const res = await this.$axios.post('/login.php', this.form);
          const data = res.data;

          if (data.success && data.user) {
            const user = data.user;
            
            // --- 💡 จุดแก้ไข ---
            // เพิ่มการบันทึก avatar_url
            localStorage.setItem('edukris_id', user.user_id);
            localStorage.setItem('edukris_name', user.username);
            localStorage.setItem('edukris_email', user.email);
            localStorage.setItem('edukris_gender', user.gender);
            localStorage.setItem('edukris_interests', user.interest);
            localStorage.setItem('edukris_avatar', user.avatar_url); // เพิ่มบรรทัดนี้
            if (user.role) {
              localStorage.setItem('edukris_role', user.role);
            }
            // --- จบจุดแก้ไข ---
            
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
        }
      }
    },
    resetForm() {
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
      this.form = { email: '', password: '' };
      this.error = null;
    }
  }
}
</script>