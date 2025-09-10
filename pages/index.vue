<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <v-sheet color="transparent" class="hero-sheet">
      <v-container class="py-16 text-center">
        <v-row align="center" justify="center">
          <v-col cols="12" md="8">
            <div class="glass-morphism-panel">
              <h1 class="text-h2 font-weight-bold mb-4 text-white">
                Transform Your Future with Education
              </h1>
              <p class="text-h5 mb-8 text-white op-8">
                Join the Robloxian online community and start your learning journey today.
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
                  dark
                  color="white"
                >
                  Learn More
                </v-btn>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-sheet>

    <!-- Features Section -->
    <v-container class="mt-16">
      <v-row>
        <v-col cols="12" class="text-center mb-8">
          <h2 class="text-h3 font-weight-bold">Why Choose Edukris?</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="4">
          <v-card class="feature-card" elevation="0" outlined>
            <v-card-text class="text-center pa-8">
              <v-icon x-large color="primary" class="mb-4">mdi-school-outline</v-icon>
              <h3 class="text-h5 font-weight-bold mb-4">Expert Instructors</h3>
              <p>Learn from industry professionals and experienced educators who are passionate about teaching.</p>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card class="feature-card" elevation="0" outlined>
            <v-card-text class="text-center pa-8">
              <v-icon x-large color="primary" class="mb-4">mdi-laptop-chromebook</v-icon>
              <h3 class="text-h5 font-weight-bold mb-4">Flexible Online Learning</h3>
              <p>Access your courses anytime, anywhere with our modern and responsive learning platform.</p>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card class="feature-card" elevation="0" outlined>
            <v-card-text class="text-center pa-8">
              <v-icon x-large color="primary" class="mb-4">mdi-certificate-outline</v-icon>
              <h3 class="text-h5 font-weight-bold mb-4">Valuable Certification</h3>
              <p>Earn recognized certificates to showcase your new skills and enhance your career profile.</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- User Count Section -->
    <v-row class="mt-16 user-count-section text-white">
      <v-col cols="12" class="text-center">
        <h2 class="text-h3 font-weight-bold">Join Our Growing Community</h2>
        <p class="text-h2 mt-4 font-weight-bold primary--text">
          <span class="user-count-number">{{ animatedUserCount }}</span>
        </p>
         <p class="text-h5 mt-2">Registered Users and counting!</p>
      </v-col>
    </v-row>

    <!-- Testimonials Section -->
    <v-container class="mt-16">
       <v-row>
        <v-col cols="12" class="text-center mb-8">
          <h2 class="text-h3 font-weight-bold">What Our Students Say</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="6">
          <v-card class="testimonial-card" elevation="0" outlined>
            <v-card-text class="pa-6">
              <v-list-item>
                <v-list-item-avatar color="grey lighten-2">
                  <v-img src="https://randomuser.me/api/portraits/women/72.jpg" alt="Avatar"></v-img>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold">Jane Doe</v-list-item-title>
                  <v-list-item-subtitle>Student</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <p class="mt-4 mb-0">"An incredible learning experience! The platform is intuitive and the instructors are top-notch. I've learned so much and feel more confident in my skills."</p>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card class="testimonial-card" elevation="0" outlined>
            <v-card-text class="pa-6">
              <v-list-item>
                <v-list-item-avatar color="grey lighten-2">
                  <v-img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Avatar"></v-img>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="font-weight-bold">John Smith</v-list-item-title>
                  <v-list-item-subtitle>Developer</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <p class="mt-4 mb-0">"Edukris helped me switch careers. The courses are relevant, practical, and the community is very supportive. Highly recommended!"</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

     <!-- Footer -->
    <v-footer class="mt-16 pa-8" dark>
      <v-container>
        <v-row>
          <v-col cols="12" md="4">
            <h3 class="text-h5 font-weight-bold mb-4">Edukris</h3>
            <p>Your gateway to knowledge and skill development. Join us to unlock your potential.</p>
          </v-col>
          <v-col cols="12" md="4">
            <h4 class="text-h6 font-weight-bold mb-4">Quick Links</h4>
            <ul class="footer-links">
              <li><a href="#">Courses</a></li>
              <li><a href="#">About Us</a></li>
              <li><a href="#">Contact</a></li>
              <li><a href="#">FAQ</a></li>
            </ul>
          </v-col>
          <v-col cols="12" md="4">
            <h4 class="text-h6 font-weight-bold mb-4">Follow Us</h4>
            <v-btn icon class="mr-2"><v-icon>mdi-facebook</v-icon></v-btn>
            <v-btn icon class="mr-2"><v-icon>mdi-twitter</v-icon></v-btn>
            <v-btn icon class="mr-2"><v-icon>mdi-linkedin</v-icon></v-btn>
            <v-btn icon><v-icon>mdi-instagram</v-icon></v-btn>
          </v-col>
        </v-row>
        <v-divider class="my-4"></v-divider>
        <div class="text-center">
          &copy; {{ new Date().getFullYear() }} Edukris. All Rights Reserved.
        </div>
      </v-container>
    </v-footer>

  </div>
</template>

<script>
export default {
  name: 'IndexPage',
  data() {
    return {
      userCount: 0,
      animatedUserCount: 0,
    }
  },
  watch: {
    userCount(newValue) {
      const animationDuration = 1500; // in ms
      const frameDuration = 1000 / 60;
      const totalFrames = Math.round(animationDuration / frameDuration);
      let frame = 0;

      const counter = setInterval(() => {
        frame++;
        const progress = frame / totalFrames;
        this.animatedUserCount = Math.round(newValue * progress);

        if (frame === totalFrames) {
          clearInterval(counter);
        }
      }, frameDuration);
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
.landing-page {
  background-color: #f5f5f5;
}

.hero-sheet {
  padding: 6rem 0;
  background: linear-gradient(135deg, #6e8efb, #a777e3);
  color: white;
}

.glass-morphism-panel {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.text-white {
  color: white !important;
}

.op-8 {
  opacity: 0.8;
}

.feature-card {
  border-radius: 16px;
  transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
  border: 1px solid #e0e0e0;
}

.feature-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}

.user-count-section {
  background: url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1351&q=80') no-repeat center center;
  background-size: cover;
  position: relative;
  padding: 6rem 0;
}

.user-count-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
}

.user-count-number {
  font-size: 5rem;
  font-weight: 900;
}

.testimonial-card {
  border-radius: 16px;
  border: 1px solid #e0e0e0;
}

.footer-links {
  list-style: none;
  padding: 0;
}

.footer-links li a {
  color: white;
  text-decoration: none;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.footer-links li a:hover {
  opacity: 1;
}
</style>
