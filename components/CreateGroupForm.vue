<template>
  <v-dialog v-model="dialog" max-width="500px">
    <v-card>
      <v-card-title>
        <span class="text-h5">สร้างกลุ่มใหม่</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="valid">
          <v-text-field
            v-model="group.name"
            label="ชื่อกลุ่ม*"
            :rules="[v => !!v || 'กรุณาใส่ชื่อกลุ่ม']"
            required
          ></v-text-field>
          <v-textarea
            v-model="group.description"
            label="คำอธิบายกลุ่ม (ไม่จำเป็น)"
            rows="3"
            auto-grow
          ></v-textarea>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="close">ยกเลิก</v-btn>
        <v-btn color="primary" :disabled="!valid" :loading="loading" @click="createGroup">สร้างกลุ่ม</v-btn>
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