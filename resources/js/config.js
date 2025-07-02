// Frontend configuration
export const config = {
  // Get base URL from Laravel's config (multiple fallbacks)
  baseURL: import.meta.env.VITE_APP_URL ||
           document.querySelector('meta[name="app-url"]')?.getAttribute('content') ||
           window.location.origin,

  apiURL: (import.meta.env.VITE_APP_URL ||
           document.querySelector('meta[name="app-url"]')?.getAttribute('content') ||
           window.location.origin) + '/api',
}

export default config;
