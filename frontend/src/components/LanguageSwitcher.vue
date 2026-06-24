<template>
  <div class="relative" ref="dropRef">
    <button
      @click="open = !open"
      class="flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors px-2 py-1.5 rounded-lg hover:bg-gray-100"
    >
      <span class="text-base leading-none">{{ currentLang.flag }}</span>
      <span class="hidden sm:inline">{{ currentLang.label }}</span>
      <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="open"
        class="absolute end-0 top-full mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 py-1 min-w-[148px] z-50 origin-top-right"
      >
        <button
          v-for="lang in languages"
          :key="lang.code"
          @click="select(lang.code)"
          class="w-full flex items-center gap-2.5 px-3 py-2 text-sm transition-colors"
          :class="lang.code === store.current
            ? 'text-primary-600 font-semibold bg-primary-50/60'
            : 'text-gray-700 hover:bg-gray-50'"
        >
          <span class="text-base leading-none">{{ lang.flag }}</span>
          {{ lang.label }}
          <svg
            v-if="lang.code === store.current"
            class="w-3.5 h-3.5 ms-auto text-primary-500"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useLanguageStore } from '@/stores/language'

const store   = useLanguageStore()
const open    = ref(false)
const dropRef = ref(null)

const languages = [
  { code: 'en', label: 'English',  flag: '🇬🇧' },
  { code: 'ar', label: 'العربية', flag: '🇸🇦' },
  { code: 'fr', label: 'Français', flag: '🇫🇷' },
  { code: 'es', label: 'Español',  flag: '🇪🇸' },
]

const currentLang = computed(() => languages.find(l => l.code === store.current) ?? languages[0])

function select(code) {
  store.setLanguage(code)
  open.value = false
}

function onClickOutside(e) {
  if (dropRef.value && !dropRef.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>
