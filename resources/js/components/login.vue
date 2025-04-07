<!-- resources/js/components/Login.vue -->
<template>
  <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <h2 class="text-center mb-4">Login</h2>
      <form @submit.prevent="login">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input
            type="email"
            id="email"
            v-model="email"
            class="form-control"
            required
          />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            v-model="password"
            class="form-control"
            required
          />
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>
      <div v-if="error" class="text-danger mt-3">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "Login",
  data() {
    return {
      email: '',
      password: '',
      error: ''
    };
  },
  methods: {
    async login() {
      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password
        });
        const token = response.data.data.token;
        localStorage.setItem('auth_token', token);
        // Redirect to Home after login
        this.$router.push('/');
      } catch (err) {
        this.error = err.response?.data?.message || 'Invalid credentials';
      }
    }
  }
}
</script>

<style scoped>
</style>