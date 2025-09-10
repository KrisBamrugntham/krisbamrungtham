<template>
  <div>
    <v-row class="mt-16 pt-16">
      <v-col cols="12" md="6" class="d-flex flex-column justify-center">
        <h1 class="text-h2 font-weight-bold mb-4">
          Transform Your Future with Education
        </h1>
        <p class="text-h5 mb-6">
          Robloxian online
        </p>
        <div>
          <v-btn
            x-large
            color="primary"
            class="mr-4"
            elevation="2"
            @click="handleGetStarted"
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
      <v-col cols="12" class="text-center">
        <h2 class="text-h3 font-weight-bold">Registered Users</h2>
        <p class="text-h4 mt-4 font-weight-light primary--text">
          {{ userCount }} people
        </p>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'IndexPage',
  data() {
    return {
      userCount: 0
    }
  },
  async mounted() {
    try {
      const res = await this.$axios.get('/get_users.php')
      const data = res.data
      if (data.status === 'success') {
        this.userCount = data.count
      } else {
        console.error('Failed to fetch user count:', data.message || 'Unknown error')
      }
    } catch (err) {
      console.error('Failed to connect to the server:', err)
    }
  },
  methods: {
    handleGetStarted() {
      const isLoggedIn = !!localStorage.getItem('edukris_name');
      if (isLoggedIn) {
        this.$router.push('/index-login');
      } else {
        this.$root.$emit('show-register-dialog');
      }
    }
  }
}
</script>

<style scoped>
.v-application {
  background-color: white !important;
}
</style>