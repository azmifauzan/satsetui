/**
 * Theme Management System
 * 
 * Supports dark/light themes with system preference detection.
 * DEFAULT THEME: Light mode (not system preference)
 * Theme preference is persisted to localStorage.
 * 
 * Usage:
 * import { useTheme } from '@/lib/theme';
 * const { theme, setTheme, toggleTheme } = useTheme();
 */

import { ref, computed } from 'vue';

export type Theme = 'light' | 'dark' | 'system';
export type ResolvedTheme = 'light' | 'dark';

// Global reactive theme state - defaults to 'dark'
const currentTheme = ref<Theme>('dark');
const systemTheme = ref<ResolvedTheme>('dark');

/**
 * Get system theme preference
 */
function getSystemTheme(): ResolvedTheme {
  if (typeof window === 'undefined') return 'light';
  
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

/**
 * Apply theme to document
 */
function applyTheme(theme: ResolvedTheme): void {
  if (typeof document === 'undefined') return;
  
  const root = document.documentElement;
  
  if (theme === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }
}

/**
 * Get resolved theme (converts 'system' to actual theme)
 */
function getResolvedTheme(theme: Theme): ResolvedTheme {
  if (theme === 'system') {
    return systemTheme.value;
  }
  return theme;
}

/**
 * Initialize theme from localStorage or system preference
 */
export function initTheme(): void {
  // Get system theme
  systemTheme.value = getSystemTheme();
  
  // Watch system theme changes
  if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
      systemTheme.value = e.matches ? 'dark' : 'light';
      if (currentTheme.value === 'system') {
        applyTheme(systemTheme.value);
      }
    });
  }
  
  // Load saved theme
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('app-theme') as Theme;
    if (saved && (saved === 'light' || saved === 'dark' || saved === 'system')) {
      currentTheme.value = saved;
    }
  }
  
  // Apply initial theme
  const resolved = getResolvedTheme(currentTheme.value);
  applyTheme(resolved);
}

/**
 * Set theme
 */
export function setTheme(theme: Theme): void {
  currentTheme.value = theme;
  
  // Persist to localStorage
  if (typeof window !== 'undefined') {
    localStorage.setItem('app-theme', theme);
  }
  
  // Apply theme
  const resolved = getResolvedTheme(theme);
  applyTheme(resolved);
}

/**
 * Toggle between light and dark themes
 */
export function toggleTheme(): void {
  const resolved = getResolvedTheme(currentTheme.value);
  const newTheme: Theme = resolved === 'dark' ? 'light' : 'dark';
  setTheme(newTheme);
}

/**
 * Main theme composable
 */
export function useTheme() {
  const resolvedTheme = computed(() => getResolvedTheme(currentTheme.value));
  
  return {
    theme: currentTheme,
    resolvedTheme,
    setTheme,
    toggleTheme,
    isDark: computed(() => resolvedTheme.value === 'dark'),
  };
}
