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
           <v-text-field
            v-model="group.image_url"
            label="URL รูปภาพกลุ่ม (ไม่จำเป็น)"
            prepend-inner-icon="mdi-image-outline"
            outlined
          ></v-text-field>
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
        image_url: '', // Add image_url to the group object
      },
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
      this.group = { name: '', description: '', image_url: '' }; // Reset all fields
      this.dialog = false;
    },
    async createGroup() {
      if (!this.$refs.form.validate()) return;
      
      this.loading = true;
      const userId = localStorage.getItem('edukris_id');

      try {
        const res = await this.$axios.post('/create_group.php', {
          group_name: this.group.name,
          description: this.group.description,
          image_url: this.group.image_url, // Send image_url to backend
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
        alert('การเชื่อมต่อล้มเหลว ไม่สามารถสร้างกลุ่มได้');
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
