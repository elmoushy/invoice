// resources/js/composables/useApi.js
import { ref } from 'vue'
import config from '../config'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const get = async (endpoint) => {
    loading.value = true
    error.value = null
    try {
      const response = await window.axios.get(endpoint)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'An error occurred'
      throw err
    } finally {
      loading.value = false
    }
  }

  const post = async (endpoint, data) => {
    loading.value = true
    error.value = null
    try {
      const response = await window.axios.post(endpoint, data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'An error occurred'
      throw err
    } finally {
      loading.value = false
    }
  }

  const put = async (endpoint, data) => {
    loading.value = true
    error.value = null
    try {
      const response = await window.axios.put(endpoint, data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'An error occurred'
      throw err
    } finally {
      loading.value = false
    }
  }

  const del = async (endpoint) => {
    loading.value = true
    error.value = null
    try {
      const response = await window.axios.delete(endpoint)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'An error occurred'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    get,
    post,
    put,
    delete: del,
    baseURL: config.baseURL,
    apiURL: config.apiURL
  }
}
