<script setup lang="ts">
import AuthForm from './components/AuthForm.vue'
import AuthLayout from './components/AuthLayout.vue'

defineOptions({ name: 'RegisterView' })
</script>

<template>
  <AuthLayout mode="register">
    <AuthForm mode="register" />
  </AuthLayout>
</template>
