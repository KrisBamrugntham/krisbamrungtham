<template>
  <v-dialog v-model="dialog" max-width="700px">
    <v-card rounded="lg" class="pa-4">
      <v-card-title class="justify-center">
        <span class="text-h5 font-weight-bold">แก้ไขข้อมูลผู้ใช้</span>
      </v-card-title>
      <v-card-subtitle class="text-center mt-1">@{{ form.username }}</v-card-subtitle>

      <v-card-text class="mt-4">
        <v-form ref="form" v-model="valid">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field v-model="form.username" label="ชื่อผู้ใช้*" :rules="[v => !!v || 'Username is required']" required outlined prepend-inner-icon="mdi-account-outline"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="form.email" label="อีเมล*" :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'E-mail must be valid']" required outlined prepend-inner-icon="mdi-email-outline"></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-text-field v-model="form.interest" label="ความสนใจ (คั่นด้วยลูกน้ำ)" outlined prepend-inner-icon="mdi-heart-outline"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="form.role" :items="['admin', 'member']" label="บทบาท" outlined prepend-inner-icon="mdi-shield-account-outline"></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-select v-model="form.status" :items="['active', 'suspended']" label="สถานะ" outlined prepend-inner-icon="mdi-list-status"></v-select>
            </v-col>
            <v-col v-if="form.status === 'suspended'" cols="12">
              <v-menu
                ref="dateMenu"
                v-model="dateMenu"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="form.suspended_until"
                    label="ระงับการใช้งานจนถึงวันที่"
                    prepend-inner-icon="mdi-calendar-clock"
                    readonly
                    outlined
                    v-bind="attrs"
                    v-on="on"
                    :rules="[v => !!v || 'กรุณาระบุวันที่สิ้นสุดการระงับ']"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="form.suspended_until"
                  no-title
                  @input="dateMenu = false"
                ></v-date-picker>
              </v-menu>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn text large @click="close">ยกเลิก</v-btn>
        <v-btn color="primary" large depressed :disabled="!valid" :loading="loading" @click="save">บันทึก</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'EditUserForm',
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
      loading: false,
      dateMenu: false,
      form: {},
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
          suspended_until: newUser.suspended_until ? newUser.suspended_until.split(' ')[0] : '' // Format for date picker
        };
      } else {
        this.form = {};
      }
    },
  },
  methods: {
    close() {
      this.dialog = false;
    },
    async save() {
      if (this.$refs.form.validate()) {
        this.loading = true;
        try {
          // If status is not suspended, clear the suspended_until date
          const payload = { ...this.form };
          if (payload.status !== 'suspended') {
              payload.suspended_until = null;
          }

          const res = await this.$axios.post('/update_user.php', payload);
          if (res.data.success) {
            this.$emit('user-updated');
            this.close();
          } else {
            alert('Error: ' + (res.data.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Failed to update user', error);
          alert('Failed to connect to server.');
        } finally {
            this.loading = false;
        }
      }
    },
  },
};
</script>
