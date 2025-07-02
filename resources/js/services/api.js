import axios from 'axios';

class ApiService {
  constructor() {
    this.isInitialized = false;
    this.initPromise = null;
  }

  async initialize() {
    if (this.isInitialized) return;
    if (this.initPromise) return this.initPromise;

    this.initPromise = this._doInitialize();
    return this.initPromise;
  }

  async _doInitialize() {
    try {
      // Get CSRF cookie from Sanctum
      await axios.get('/sanctum/csrf-cookie', {
        withCredentials: true,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      this.isInitialized = true;
    } catch (error) {
      console.error('Failed to initialize CSRF token:', error);
      throw error;
    }
  }

  async makeRequest(method, url, data = null, config = {}) {
    await this.initialize();

    const requestConfig = {
      ...config,
      withCredentials: true,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        ...config.headers
      }
    };

    switch (method.toLowerCase()) {
      case 'get':
        return axios.get(url, requestConfig);
      case 'post':
        return axios.post(url, data, requestConfig);
      case 'put':
        return axios.put(url, data, requestConfig);
      case 'patch':
        return axios.patch(url, data, requestConfig);
      case 'delete':
        return axios.delete(url, requestConfig);
      default:
        throw new Error(`Unsupported HTTP method: ${method}`);
    }
  }

  async get(url, config = {}) {
    return this.makeRequest('get', url, null, config);
  }

  async post(url, data = {}, config = {}) {
    return this.makeRequest('post', url, data, config);
  }

  async put(url, data = {}, config = {}) {
    return this.makeRequest('put', url, data, config);
  }

  async patch(url, data = {}, config = {}) {
    return this.makeRequest('patch', url, data, config);
  }

  async delete(url, config = {}) {
    return this.makeRequest('delete', url, null, config);
  }
}

export default new ApiService();
