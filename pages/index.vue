<template>
  <div>
    <v-row class="mt-16 pt-16">
      <v-col cols="12" md="6" class="d-flex flex-column justify-center">
        <h1 class="text-h2 font-weight-bold mb-4">
          Transform Your Future with Education
        </h1>
        <p class="text-h5 mb-6">
          Discover a world of knowledge and unlock your potential with our innovative learning platform.
        </p>
        <div>
          <v-btn
            x-large
            color="primary"
            class="mr-4"
            elevation="2"
            @click="showRegister = true"
          >
            Get Started
          </v-btn>
          <v-btn
            x-large
            outlined
            color="primary"
          >
            Learn More
          </v-btn>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-center align-center">
        <v-img
          max-width="500"
          src="https://cdn.vuetifyjs.com/images/cards/school.png"
          alt="Education illustration"
        ></v-img>
      </v-col>
    </v-row>

    <v-row class="mt-16">
      <v-col cols="12" class="text-center mb-8">
        <h2 class="text-h3 font-weight-bold">Why Choose Edukris?</h2>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="mx-auto" elevation="2">
          <v-card-text class="text-center pa-6">
            <v-icon x-large color="primary" class="mb-4">mdi-school</v-icon>
            <h3 class="text-h5 font-weight-bold mb-4">Expert Instructors</h3>
            <p>Learn from industry professionals and experienced educators.</p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="mx-auto" elevation="2">
          <v-card-text class="text-center pa-6">
            <v-icon x-large color="primary" class="mb-4">mdi-laptop</v-icon>
            <h3 class="text-h5 font-weight-bold mb-4">Online Learning</h3>
            <p>Access courses anytime, anywhere with our flexible platform.</p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="mx-auto" elevation="2">
          <v-card-text class="text-center pa-6">
            <v-icon x-large color="primary" class="mb-4">mdi-certificate</v-icon>
            <h3 class="text-h5 font-weight-bold mb-4">Certification</h3>
            <p>Earn recognized certificates upon course completion.</p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-16">
      <v-col cols="12" class="text-center mb-8">
        <h2 class="text-h3 font-weight-bold">Registered Users</h2>
      </v-col>
      <v-col v-if="users.length === 0" cols="12" class="text-center text-h6 text-grey">
        ไม่มีผู้ใช้ในระบบ
      </v-col>
      <v-col cols="12">
        <v-card elevation="2" class="pa-4">
          <v-list dense>
            <v-list-item v-for="user in users" :key="user.id">
              <v-list-item-content>
                <v-list-item-title>
                  {{ user.username }} ({{ user.email }})
                </v-list-item-title>
                <v-list-item-subtitle>
                  เพศ: {{ user.gender }} | ความสนใจ: {{ user.interest }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>

    <register-form v-model="showRegister" />
  </div>
</template>

<script>
import axios from 'axios'
import RegisterForm from '~/components/RegisterForm.vue'

export default {
  name: 'IndexPage',
  components: { RegisterForm },
  data() {
    return {
      showRegister: false,
      users: []
    }
  },
  async mounted() {
    try {
      const res = await axios.get('http://localhost/krisbamrungtham/Db/get_users.php')
      const data = res.data

      // ตรวจสอบโครงสร้างข้อมูลจาก API (สมมติส่ง {status, data})
      if (data.status === 'success' && data.data) {
        this.users = data.data
      } else {
        console.error('Failed to fetch users:', data.message || 'Unknown error')
      }
    } catch (err) {
      console.error('Failed to connect to the server:', err)
    }
  }
}
</script>

<style scoped>
.v-application {
  background-color: white !important;
}
</style>
