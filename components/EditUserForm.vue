<template>
  <v-dialog v-model="dialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">Edit User: {{ form.username }}</span>
      </v-card-title>
      <v-card-text>
        <v-container>
          <v-form ref="form" v-model="valid">
            <v-text-field v-model="form.username" label="Username*" :rules="[v => !!v || 'Username is required']" required />
            <v-text-field v-model="form.email" label="Email*" :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'E-mail must be valid']" required />
            <v-radio-group v-model="form.gender" label="Gender" row>
              <v-radio label="ชาย" value="ชาย" />
              <v-radio label="หญิง" value="หญิง" />
              <v-radio label="อื่น ๆ" value="อื่น ๆ" />
            </v-radio-group>
            <v-text-field v-model="form.interest" label="Interests (comma separated)" />
            <v-select v-model="form.role" :items="['admin', 'member']" label="Role" required />

            <v-select
              v-model="form.status"
              :items="['active', 'suspended']"
              label="Status"
              required
            ></v-select>
            
            <v-text-field
              v-if="form.status === 'suspended'"
              v-model="form.suspended_until"
              label="Suspended Until (YYYY-MM-DD)"
              placeholder="เช่น 2025-12-31"
              :rules="[v => !!v || 'กรุณาระบุวันที่สิ้นสุดการระงับ']"
              required
            ></v-text-field>
            </v-form>
        </v-container>
        <small>*indicates required field</small>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
        <v-btn color="blue darken-1" :disabled="!valid" @click="save">Save</v-btn>
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
        // แปลงค่า null ให้เป็น string ว่าง เพื่อให้ v-text-field ทำงานได้ถูกต้อง
        this.form = { 
          ...newUser,
          suspended_until: newUser.suspended_until || ''
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
        try {
          const res = await this.$axios.post('/update_user.php', this.form);
          if (res.data.success) {
            this.$emit('user-updated');
            this.close();
          } else {
            alert('Error: ' + (res.data.error || 'Unknown error'));
          }
        } catch (error) {
          console.error('Failed to update user', error);
          alert('Failed to connect to server.');
        }
      }
    },
  },
};
</script>