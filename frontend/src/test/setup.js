// Global test setup for Vitest + Vue Test Utils.
// Provides lightweight browser API shims that jsdom does not implement.

import { vi } from 'vitest'

// matchMedia is used by some components / libraries; jsdom lacks it.
if (!window.matchMedia) {
  window.matchMedia = vi.fn().mockImplementation((query) => ({
    matches: false,
    media: query,
    onchange: null,
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    addListener: vi.fn(),
    removeListener: vi.fn(),
    dispatchEvent: vi.fn(),
  }))
}

// IntersectionObserver shim (used for lazy-loading / infinite scroll).
if (!window.IntersectionObserver) {
  window.IntersectionObserver = class {
    observe() {}
    unobserve() {}
    disconnect() {}
  }
}

// Silence scrollTo not-implemented warnings from jsdom.
window.scrollTo = window.scrollTo || vi.fn()
