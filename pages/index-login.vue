<template>
  <div>
    <!-- Custom App Bar -->
    <v-app-bar color="white" elevation="2" app>
      <v-menu offset-y v-model="menu">
        <template v-slot:activator="{ on, attrs }">
          <v-btn text v-bind="attrs" v-on="on" class="px-0" style="font-size:1.5rem; font-weight:bold; color:#1976D2; text-transform:none;">
            Edukris
          </v-btn>
        </template>
        <v-list>
          <v-list-item link to="/">
            <v-list-item-title>หน้าหลัก</v-list-item-title>
          </v-list-item>
          <v-list-item link to="/courses">
            <v-list-item-title>คอร์สเรียน</v-list-item-title>
          </v-list-item>
          <v-list-item link to="/about">
            <v-list-item-title>เกี่ยวกับเรา</v-list-item-title>
          </v-list-item>
          <v-list-item link to="/contact">
            <v-list-item-title>ติดต่อ</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
      <v-spacer />
      <v-avatar size="36" class="mr-2">
        <v-img :src="userAvatar" />
      </v-avatar>
      <span class="font-weight-bold mr-4" style="font-size:1.1rem; color:#1976D2;">{{ userName }}</span>
      <v-btn color="error" small @click="logout">Logout</v-btn>
    </v-app-bar>
    <v-row no-gutters class="fill-height" style="min-height: 100vh;">
      <!-- Sidebar Left (User Info) -->
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
      <!-- Main Feed -->
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
            <v-img
              src="/schedule-demo.png"
              alt="ตารางเรียน"
              max-width="700"
              contain
            />
          </v-card-text>
        </v-card>
      </v-col>
      <!-- Sidebar Right (Contacts) -->
      <v-col cols="12" md="3" class="pa-0 grey lighten-4" style="min-width:220px;max-width:320px;">
        <div class="pt-10 px-4">
          <h3 class="text-h6 font-weight-bold mb-2">เพื่อนออนไลน์</h3>
          <v-list dense>
            <v-list-item v-for="(contact, i) in contacts" :key="i">
              <v-list-item-avatar size="36">
                <v-img :src="contact.avatar" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ contact.name }}</v-list-item-title>
              </v-list-item-content>
              <v-icon color="success" small>mdi-circle</v-icon>
            </v-list-item>
          </v-list>
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
      menu: false,
      userName: '',
      userAvatar: 'https://randomuser.me/api/portraits/men/85.jpg',
      userEmail: '',
      userGender: '',
      userInterests: '',
      contacts: [
        { name: 'Kris Bass', avatar: 'https://randomuser.me/api/portraits/men/85.jpg' },
        { name: 'Meta AI', avatar: 'https://randomuser.me/api/portraits/lego/2.jpg' },
        { name: 'เพื่อน', avatar: 'https://randomuser.me/api/portraits/men/32.jpg' },
        { name: 'กลุ่ม', avatar: 'https://randomuser.me/api/portraits/women/44.jpg' },
        { name: 'Marketplace', avatar: 'https://randomuser.me/api/portraits/men/12.jpg' }
      ]
    }
  },
  mounted() {
    this.userName = localStorage.getItem('edukris_name') || 'Guest'
    this.userEmail = localStorage.getItem('edukris_email') || '-'
    this.userGender = localStorage.getItem('edukris_gender') || '-'
    this.userInterests = localStorage.getItem('edukris_interests') || '-'
  },
  methods: {
    logout() {
      localStorage.removeItem('edukris_name')
      localStorage.removeItem('edukris_email')
      localStorage.removeItem('edukris_gender')
      localStorage.removeItem('edukris_interests')
      this.userName = ''
      this.userEmail = ''
      this.userGender = ''
      this.userInterests = ''
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
.v-list-item-avatar img {
  object-fit: cover;
}
</style>
