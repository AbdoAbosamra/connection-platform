import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    include: ['src/**/*.{test,spec}.js'],
    setupFiles: ['./src/test/setup.js'],
    coverage: {
      provider: 'v8',
      include: ['src/**/*.{js,vue}'],
      exclude: ['src/**/*.{test,spec}.js', 'src/test/**'],
    },
  },
})
