<script setup lang="ts">
import AuthForm from './components/AuthForm.vue'
import AuthLayout from './components/AuthLayout.vue'

defineOptions({ name: 'LoginView' })
</script>

<template>
  <AuthLayout mode="login">
    <AuthForm mode="login" />
  </AuthLayout>
</template>
