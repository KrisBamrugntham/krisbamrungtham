<template>
  <v-container>
    <div v-if="loading" class="text-center">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </div>

    <div v-else-if="membershipStatus === 'member'">
      <h1>Welcome to Group: {{ group.name }}</h1>
      <p>Group Description: {{ group.description }}</p>
      </div>

    <div v-else class="text-center">
      <v-card class="mx-auto pa-5" max-width="500">
        <v-card-title class="headline">
          คุณไม่ได้เป็นสมาชิกของกลุ่มนี้
        </v-card-title>
        <v-card-text>
          <div v-if="membershipStatus === 'not_member'">
            คุณต้องการส่งคำขอเพื่อเข้าร่วมกลุ่มหรือไม่?
          </div>
          <div v-if="membershipStatus === 'pending'">
            คำขอเข้าร่วมกลุ่มของคุณถูกส่งไปแล้ว และกำลังรอการอนุมัติ
          </div>
          <div v-if="membershipStatus === 'rejected'">
            คำขอเข้าร่วมกลุ่มของคุณถูกปฏิเสธ
          </div>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn
            v-if="membershipStatus === 'not_member' || membershipStatus === 'rejected'"
            color="primary"
            @click="sendJoinRequest"
            :loading="requesting"
          >
            ส่งคำขอเข้ากลุ่ม
          </v-btn>
          <v-btn text to="/groups">กลับไปหน้ารวมกลุ่ม</v-btn>
        </v-card-actions>
      </v-card>
    </div>

    <div v-if="isOwner">
        <v-divider class="my-5"></v-divider>
        <h2>Pending Join Requests</h2>
        </div>

  </v-container>
</template>

<script>
export default {
  data() {
    return {
      groupId: this.$route.params.id,
      userId: null, // สมมติว่าดึงมาจาก Vuex store หรือ localStorage
      group: {},
      loading: true,
      requesting: false,
      membershipStatus: null, // 'member', 'pending', 'rejected', 'not_member'
      isOwner: false, // เพิ่ม state สำหรับตรวจสอบว่าเป็นเจ้าของกลุ่มหรือไม่
    }
  },
  async created() {
    // 1. ดึง user ID ที่ล็อกอินอยู่
    // คุณต้องมีระบบ state management (เช่น Vuex) หรือเก็บ user ID ใน localStorage หลังล็อกอิน
    this.userId = parseInt(localStorage.getItem('user_id')); 
    if (!this.userId) {
        // ถ้าไม่ล็อกอิน ให้ redirect ไปหน้า login
        this.$router.push('/index-login');
        return;
    }

    // 2. ตรวจสอบสถานะการเป็นสมาชิก
    await this.checkStatus();

    // 3. ถ้าเป็นสมาชิก ให้ดึงข้อมูลกลุ่มมาแสดง
    if (this.membershipStatus === 'member') {
        await this.fetchGroupDetails();
    }
    
    this.loading = false;
  },
  methods: {
    async checkStatus() {
      try {
        const response = await this.$axios.get(`http://localhost/your_project_path/Db/check_group_status.php?group_id=${this.groupId}&user_id=${this.userId}`);
        this.membershipStatus = response.data.status;
      } catch (error) {
        console.error('Error checking membership status:', error);
        alert('เกิดข้อผิดพลาดในการตรวจสอบสถานะ');
      }
    },
    async fetchGroupDetails() {
        // ใส่โค้ดเดิมของคุณที่ใช้ดึงข้อมูลกลุ่ม เช่น ชื่อ, รายละเอียด, โพสต์
        // สมมติว่ามี API `get_group_details.php`
        // const response = await this.$axios.get(`.../get_group_details.php?group_id=${this.groupId}`);
        // this.group = response.data.group;
        // this.isOwner = (this.group.created_by === this.userId); // ตรวจสอบว่าเป็นเจ้าของกลุ่มหรือไม่
    },
    async sendJoinRequest() {
      this.requesting = true;
      try {
        await this.$axios.post('http://localhost/your_project_path/Db/send_join_request.php', {
          group_id: this.groupId,
          user_id: this.userId
        });
        // เมื่อส่งสำเร็จ ให้อัปเดตสถานะเป็น 'pending'
        this.membershipStatus = 'pending';
        alert('ส่งคำขอเข้าร่วมกลุ่มสำเร็จแล้ว');
      } catch (error) {
        console.error('Error sending join request:', error);
        alert('เกิดข้อผิดพลาดในการส่งคำขอ');
      } finally {
        this.requesting = false;
      }
    },
  },
}
</script>