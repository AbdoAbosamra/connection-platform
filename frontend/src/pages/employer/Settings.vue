<template>
  <div class="max-w-2xl space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-900">Settings</h1>
      <p class="text-gray-500 text-sm mt-0.5">Manage your account and password</p>
    </div>

    <!-- Account -->
    <form @submit.prevent="saveAccount" class="card p-6 space-y-5">
      <h2 class="font-bold text-gray-900 border-b border-gray-100 pb-3">Account</h2>

      <div>
        <label class="label">Full name</label>
        <input v-model="account.name" class="input" required />
        <p v-if="accountErrors.name" class="mt-1 text-xs text-rose-600">{{ accountErrors.name[0] }}</p>
      </div>
      <div>
        <label class="label">Email</label>
        <input v-model="account.email" type="email" class="input" required />
        <p v-if="accountErrors.email" class="mt-1 text-xs text-rose-600">{{ accountErrors.email[0] }}</p>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary !px-6" :disabled="savingAccount">
          {{ savingAccount ? 'Saving…' : 'Save changes' }}
        </button>
        <span v-if="accountSaved" class="text-sm text-emerald-600 font-medium">✓ Saved</span>
      </div>
    </form>

    <!-- Password -->
    <form @submit.prevent="savePassword" class="card p-6 space-y-5">
      <h2 class="font-bold text-gray-900 border-b border-gray-100 pb-3">Password</h2>

      <div>
        <label class="label">Current password</label>
        <input v-model="pw.current_password" type="password" class="input" required />
        <p v-if="pwErrors.current_password" class="mt-1 text-xs text-rose-600">{{ pwErrors.current_password[0] }}</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">New password</label>
          <input v-model="pw.password" type="password" class="input" required minlength="8" />
          <p v-if="pwErrors.password" class="mt-1 text-xs text-rose-600">{{ pwErrors.password[0] }}</p>
        </div>
        <div>
          <label class="label">Confirm new password</label>
          <input v-model="pw.password_confirmation" type="password" class="input" required />
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary !px-6" :disabled="savingPw">
          {{ savingPw ? 'Updating…' : 'Update password' }}
        </button>
        <span v-if="pwSaved" class="text-sm text-emerald-600 font-medium">✓ Password updated</span>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { authApi } from '@/api/auth'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const account = reactive({ name: '', email: '' })
const accountErrors = ref({})
const savingAccount = ref(false)
const accountSaved  = ref(false)

const pw = reactive({ current_password: '', password: '', password_confirmation: '' })
const pwErrors = ref({})
const savingPw = ref(false)
const pwSaved  = ref(false)

onMounted(() => {
  account.name  = auth.user?.name ?? ''
  account.email = auth.user?.email ?? ''
})

async function saveAccount() {
  savingAccount.value = true
  accountSaved.value  = false
  accountErrors.value = {}
  try {
    const { data } = await authApi.updateAccount({ name: account.name, email: account.email })
    auth.user = data.user
    accountSaved.value = true
  } catch (err) {
    accountErrors.value = err.response?.data?.errors ?? {}
  } finally {
    savingAccount.value = false
  }
}

async function savePassword() {
  savingPw.value = true
  pwSaved.value  = false
  pwErrors.value = {}
  try {
    await authApi.updatePassword({ ...pw })
    pwSaved.value = true
    pw.current_password = pw.password = pw.password_confirmation = ''
  } catch (err) {
    pwErrors.value = err.response?.data?.errors ?? {}
  } finally {
    savingPw.value = false
  }
}
</script>
