import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'

export default [
  {
    ignores: ['dist/**', 'node_modules/**', 'coverage/**', 'public/**'],
  },
  js.configs.recommended,
  // 'flat/essential' enforces only correctness rules (no-parsing-error,
  // valid-template-root, no-unused-components, etc.) without the opinionated
  // whitespace/attribute-formatting rules that 'flat/recommended' adds. For an
  // existing codebase we want lint to surface real bugs, not 2500 style nits —
  // formatting is left to the editor / Prettier.
  ...pluginVue.configs['flat/essential'],
  {
    files: ['**/*.{js,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        localStorage: 'readonly',
        sessionStorage: 'readonly',
        navigator: 'readonly',
        console: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly',
        setInterval: 'readonly',
        clearInterval: 'readonly',
        fetch: 'readonly',
        FormData: 'readonly',
        URL: 'readonly',
        Blob: 'readonly',
        alert: 'readonly',
        confirm: 'readonly',
        process: 'readonly',
      },
    },
    rules: {
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'off',
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
    },
  },
  {
    // Build/config files run in Node and use CommonJS globals.
    files: ['*.config.js', 'tailwind.config.js', 'postcss.config.js'],
    languageOptions: {
      globals: {
        require: 'readonly',
        module: 'readonly',
        process: 'readonly',
        __dirname: 'readonly',
      },
    },
  },
  {
    files: ['**/*.{test,spec}.js', 'src/test/**'],
    languageOptions: {
      globals: {
        vi: 'readonly',
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        afterEach: 'readonly',
        beforeAll: 'readonly',
        afterAll: 'readonly',
      },
    },
  },
]
