import axios from 'axios';

const defaultApiUrl = `${window.location.protocol}//${window.location.hostname || 'localhost'}:8000/api`;

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || defaultApiUrl,
});

export default api;
