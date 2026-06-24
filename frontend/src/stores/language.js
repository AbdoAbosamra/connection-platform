import { defineStore } from 'pinia'
import { ref } from 'vue'
import { translations } from '@/i18n/translations'

export const useLanguageStore = defineStore('language', () => {
  const stored = typeof localStorage !== 'undefined' ? localStorage.getItem('lang') : null
  const current = ref(stored || 'en')

  function setLanguage(lang) {
    current.value = lang
    localStorage.setItem('lang', lang)
    document.documentElement.lang = lang
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr'
  }

  function t(key) {
    const keys = key.split('.')
    // Accessing current.value creates a reactive dependency so templates re-render on language change
    let val = translations[current.value]
    for (const k of keys) val = val?.[k]
    if (val !== undefined) return val
    let fallback = translations.en
    for (const k of keys) fallback = fallback?.[k]
    return fallback ?? key
  }

  // Apply stored preference on init
  document.documentElement.dir = current.value === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = current.value

  return { current, setLanguage, t }
})
