// resources/js/services/AppService.js
class AppService {
  constructor() {
    this.config = {
      baseURL: this.getBaseURL(),
      apiURL: this.getBaseURL() + '/api'
    }
  }

  getBaseURL() {
    // Try to get from meta tag first
    const metaUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content')
    if (metaUrl) {
      return metaUrl
    }

    // Fallback to window location
    return window.location.origin
  }

  getConfig() {
    return this.config
  }

  url(path = '') {
    return this.config.baseURL + (path.startsWith('/') ? path : '/' + path)
  }

  apiUrl(path = '') {
    return this.config.apiURL + (path.startsWith('/') ? path : '/' + path)
  }
}

export default new AppService()
