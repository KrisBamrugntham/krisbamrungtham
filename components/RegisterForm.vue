<template>
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-card elevation="3" class="pa-6">
      <v-card-title class="headline font-weight-bold mb-2">สมัครสมาชิก Edukris</v-card-title>
      <v-form ref="form" v-model="valid" lazy-validation>
        <v-text-field
          v-model="form.name"
          :rules="[v => !!v || 'กรุณากรอกชื่อ']"
          label="ชื่อ"
          required
        />
        <v-text-field
          v-model="form.email"
          :rules="emailRules"
          label="อีเมล"
          required
        />
        <v-text-field
          v-model="form.password"
          :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :type="showPassword ? 'text' : 'password'"
          @click:append="showPassword = !showPassword"
          :rules="[v => !!v || 'กรุณากรอกรหัสผ่าน', v => v.length >= 6 || 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']"
          label="รหัสผ่าน"
          required
        />
        <v-text-field
          v-model="form.confirmPassword"
          :append-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :type="showConfirmPassword ? 'text' : 'password'"
          @click:append="showConfirmPassword = !showConfirmPassword"
          :rules="[v => !!v || 'กรุณายืนยันรหัสผ่าน', v => v === form.password || 'รหัสผ่านไม่ตรงกัน']"
          label="ยืนยันรหัสผ่าน"
          required
        />
        <v-radio-group v-model="form.gender" :rules="[v => !!v || 'กรุณาเลือกเพศ']" label="เพศ" row>
          <v-radio label="ชาย" value="ชาย" />
          <v-radio label="หญิง" value="หญิง" />
          <v-radio label="อื่น ๆ" value="อื่น ๆ" />
        </v-radio-group>
        <v-select
          v-model="form.interests"
          :items="interestOptions"
          label="ความสนใจ"
          multiple
          chips
          :rules="[v => v.length > 0 || 'กรุณาเลือกความสนใจอย่างน้อย 1 รายการ']"
          required
        />
        <v-btn
          color="primary"
          class="mt-4"
          large
          @click="submit()"
          :disabled="!valid"
        >
          ลงทะเบียน
        </v-btn>
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
        <v-alert
          v-if="success"
          type="success"
          class="mt-4"
          border="left"
          colored-border
          elevation="2"
        >
          ลงทะเบียนสำเร็จ
        </v-alert>
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
        const payload = {
          username: this.form.name,
          email: this.form.email,
          password: this.form.password,
          gender: this.form.gender,
          interests: this.form.interests.join(','),
          avatar_url: 'ssdfefsef'
        };
        try {
          // ใช้ this.$axios.post และเรียกใช้ path แค่ '/insert.php'
          const res = await this.$axios.post('/insert.php', payload);
          const data = res.data;
          
          if (data.success) {
            this.success = true;
            localStorage.setItem('edukris_name', this.form.name)
            localStorage.setItem('edukris_email', this.form.email)
            localStorage.setItem('edukris_gender', this.form.gender)
            localStorage.setItem('edukris_interests', this.form.interests.join(','))
            setTimeout(() => {
              this.dialog = false
              this.$router.push('/index-login')
            }, 1200)
          } else {
            this.error = data.error || 'เกิดข้อผิดพลาดในการลงทะเบียน';
          }
        } catch (err) {
          this.error = 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้';
        }
      }
    },
    resetForm() {
      this.valid = false;
      this.success = false;
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