import { defineStore } from 'pinia'
import { ref } from 'vue'
import { professionalsApi } from '@/api/professionals'

export const useProfessionalsStore = defineStore('professionals', () => {
  const professionals    = ref([])
  const currentProfile   = ref(null)
  const pagination       = ref(null)
  const loading          = ref(false)

  /**
   * Search/list professionals with optional filters.
   * Strips all falsy values before sending so the backend
   * never receives spurious empty-string / false params.
   */
  async function search(filters = {}) {
    loading.value = true
    try {
      const clean = Object.fromEntries(
        Object.entries(filters).filter(([, v]) => v !== '' && v !== false && v !== null && v !== undefined)
      )
      const { data } = await professionalsApi.list(clean)
      professionals.value = data.data
      pagination.value    = data
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single professional profile by ID.
   */
  async function fetchProfile(id) {
    loading.value = true
    try {
      const { data } = await professionalsApi.get(id)
      currentProfile.value = data.professional
    } finally {
      loading.value = false
    }
  }

  function clearCurrentProfile() {
    currentProfile.value = null
  }

  return {
    professionals,
    currentProfile,
    pagination,
    loading,
    search,
    fetchProfile,
    clearCurrentProfile,
  }
})
