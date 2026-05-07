import { defineStore } from 'pinia'
import { ref } from 'vue'
import { jobsApi } from '@/api/jobs'

export const useJobsStore = defineStore('jobs', () => {
  const jobs        = ref([])
  const currentJob  = ref(null)
  const pagination  = ref(null)
  const loading     = ref(false)
  const savedIds    = ref(new Set())

  async function search(filters = {}) {
    loading.value = true
    try {
      const { data } = await jobsApi.list(filters)
      jobs.value      = data.data
      pagination.value = data
    } finally {
      loading.value = false
    }
  }

  async function fetchJob(slug) {
    loading.value = true
    try {
      const { data } = await jobsApi.get(slug)
      currentJob.value = data.job
    } finally {
      loading.value = false
    }
  }

  async function toggleSave(jobId) {
    if (savedIds.value.has(jobId)) {
      await jobsApi.unsave(jobId)
      savedIds.value.delete(jobId)
    } else {
      await jobsApi.save(jobId)
      savedIds.value.add(jobId)
    }
  }

  return { jobs, currentJob, pagination, loading, savedIds, search, fetchJob, toggleSave }
})
