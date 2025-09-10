<template>
  <v-app>
    <v-app-bar app color="white" elevation="2">
      <v-toolbar-title class="font-weight-bold primary--text" style="cursor: pointer" @click="$router.push('/')">
        Edukris
      </v-toolbar-title>
      <v-spacer />

      <div v-if="!isLoggedIn">
        <v-btn text color="primary" @click="showLogin">
          Sign In
        </v-btn>
        <v-btn color="primary" elevation="2" @click="showRegister">
          Sign Up
        </v-btn>
      </div>

      <div v-else class="d-flex align-center">
        <v-btn v-if="userRole === 'admin'" small text class="mr-2" to="/index-admin">
          <v-icon left>mdi-view-dashboard</v-icon>
          Admin Panel
        </v-btn>
        
        <v-btn small text class="mr-2" to="/chat">
          <v-icon left>mdi-chat</v-icon>
          แชท
        </v-btn>

        <v-btn small text class="mr-2" to="/groups">
          <v-icon left>mdi-account-group</v-icon>
          กลุ่ม
        </v-btn>
        
        <v-avatar size="36" class="mr-2">
          <v-img :src="userAvatar" />
        </v-avatar>
        <span class="font-weight-bold mr-4" style="font-size:1.1rem; color:#1976D2;">
          {{ userName }}
        </span>
        <v-btn color="error" small @click="logout">
          Logout
        </v-btn>
      </div>
    </v-app-bar>

    <v-main>
      <v-container>
        <nuxt />
      </v-container>
    </v-main>

    <v-footer app>
      <span>&copy; {{ new Date().getFullYear() }}</span>
    </v-footer>

    <register-form v-model="registerDialog" @switch-to-login="showLogin" />
    <login-form v-model="loginDialog" @switch-to-register="showRegister" />
  </v-app>
</template>

<script>
import RegisterForm from '~/components/RegisterForm.vue'
import LoginForm from '~/components/LoginForm.vue'

export default {
  components: { RegisterForm, LoginForm },
  data () {
    return {
      isLoggedIn: false,
      userName: '',
      userAvatar: 'https://randomuser.me/api/portraits/men/85.jpg',
      userRole: '',
      registerDialog: false,
      loginDialog: false,
    }
  },
  mounted() {
    this.checkLoginStatus();
    window.addEventListener('storage', this.checkLoginStatus);
    this.$root.$on('show-register-dialog', this.showRegister);
  },
  beforeDestroy() {
    window.removeEventListener('storage', this.checkLoginStatus);
    this.$root.$off('show-register-dialog', this.showRegister);
  },
  methods: {
    checkLoginStatus() {
      const name = localStorage.getItem('edukris_name');
      this.isLoggedIn = !!name;
      this.userName = name || '';
      this.userRole = localStorage.getItem('edukris_role') || '';
      this.userAvatar = localStorage.getItem('edukris_avatar') || 'https://randomuser.me/api/portraits/men/85.jpg';
    },
    logout() {
      localStorage.clear();
      this.isLoggedIn = false;
      this.userName = '';
      this.userRole = '';
      this.userAvatar = 'https://randomuser.me/api/portraits/men/85.jpg'; // Reset to default
      if (this.$route.path !== '/') {
        this.$router.push('/');
      } else {
        window.location.reload();
      }
    },
    showLogin() {
      this.registerDialog = false;
      this.loginDialog = true;
    },
    showRegister() {
      this.loginDialog = false;
      this.registerDialog = true;
    }
  }
}
</script>