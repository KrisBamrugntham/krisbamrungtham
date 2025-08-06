<template>
  <div>
    <v-row no-gutters class="fill-height" style="min-height: calc(100vh - 64px);">
      <v-col cols="12" md="3" class="pa-0 grey lighten-4" style="min-width:260px;max-width:320px;">
        <v-list dense class="pt-10">
          <v-list-item>
            <v-list-item-avatar size="56">
              <v-img :src="userAvatar" />
            </v-list-item-avatar>
            <v-list-item-content>
              <v-list-item-title class="font-weight-bold" style="font-size:1.1rem;">{{ userName }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-card class="mx-4 my-4 pa-4" outlined>
          <div class="mb-2"><strong>ชื่อผู้ใช้:</strong> {{ userName }}</div>
          <div class="mb-2"><strong>อีเมล:</strong> {{ userEmail }}</div>
          <div class="mb-2"><strong>เพศ:</strong> {{ userGender }}</div>
          <div><strong>ความสนใจ:</strong> {{ userInterests }}</div>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6" class="pa-6">
        <h2 class="text-h4 font-weight-bold mb-6">Welcome, {{ userName }}</h2>
        <v-card class="mb-6" elevation="2">
          <v-card-title class="font-weight-bold">Edukris Feed</v-card-title>
          <v-card-text>
            <p>ยินดีต้อนรับสู่ Edukris! ที่นี่คุณจะได้พบกับข่าวสาร กิจกรรม และคอร์สเรียนใหม่ ๆ</p>
          </v-card-text>
        </v-card>
        <v-card class="mb-6" elevation="1">
          <v-card-title>ตารางเรียน</v-card-title>
          <v-card-text class="d-flex justify-center">
            <v-img src="/schedule-demo.jpg" alt="ตารางเรียน" max-width="700" contain />
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3" class="pa-0 grey lighten-4" style="min-width:220px;max-width:320px;">
        <div class="pt-10 px-4">
          <h3 class="text-h6 font-weight-bold mb-2">เพื่อนออนไลน์</h3>
          <v-list dense v-if="friends.length > 0">
            <v-list-item v-for="friend in friends" :key="friend.user_id">
              <v-list-item-avatar size="36">
                <v-img :src="friend.avatar_url || 'https://randomuser.me/api/portraits/lego/2.jpg'" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ friend.username }}</v-list-item-title>
              </v-list-item-content>
              <v-icon color="success" small>mdi-circle</v-icon>
            </v-list-item>
          </v-list>
          <div v-else class="pa-4 text-center grey--text">
            ไม่มีเพื่อนที่ออนไลน์
          </div>
        </div>
      </v-col>
      </v-row>
  </div>
</template>

<script>
export default {
  name: 'IndexLogin',
  data() {
    return {
      userName: '',
      userAvatar: 'https://randomuser.me/api/portraits/men/85.jpg',
      userEmail: '',
      userGender: '',
      userInterests: '',
      friends: [] // เปลี่ยนจาก contacts เป็น friends และเริ่มต้นเป็น array ว่าง
    }
  },
  mounted() {
    this.loadUserData();
    this.fetchFriends(); // เรียกใช้ method ใหม่เพื่อดึงข้อมูลเพื่อน
  },
  methods: {
    loadUserData() {
      // ดึงข้อมูลจาก localStorage มาแสดงผล
      this.userName = localStorage.getItem('edukris_name') || 'Guest';
      this.userEmail = localStorage.getItem('edukris_email') || '-';
      this.userGender = localStorage.getItem('edukris_gender') || '-';
      this.userInterests = localStorage.getItem('edukris_interests') || '-';
      
      // Redirect ถ้าไม่มีข้อมูล login
      if (!localStorage.getItem('edukris_name')) {
        this.$router.push('/');
      }
    },
    // เพิ่ม method ใหม่สำหรับดึงข้อมูลเพื่อนจาก API
    async fetchFriends() {
      const userId = localStorage.getItem('edukris_id');
      if (!userId) return; // ถ้าไม่มี user id ก็ไม่ต้องทำอะไรต่อ

      try {
        const res = await this.$axios.get(`/get_friends.php?user_id=${userId}`);
        if (res.data.status === 'success') {
          this.friends = res.data.data; // นำข้อมูลเพื่อนที่ได้จาก API มาใส่ใน friends array
        } else {
          console.error('Failed to fetch friends:', res.data.message);
        }
      } catch (error) {
        console.error('Error fetching friends:', error);
      }
    }
  }
}
</script>

<style scoped>
.v-list-item-avatar img {
  object-fit: cover;
}
</style>