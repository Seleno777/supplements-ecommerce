import axios, { AxiosInstance } from 'axios';

// Configuración SIMPLE para Laravel + Inertia con TypeScript
const instance: AxiosInstance = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Solo para desarrollo local - ajustar según tu configuración
instance.defaults.withCredentials = true;

// Agregar token CSRF automáticamente si existe
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
  instance.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

// Interceptor para manejar errores CSRF automáticamente
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 419) { // Token mismatch
      console.warn('CSRF token mismatch, recargando página...');
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

// Declaración global para TypeScript
declare global {
  interface Window {
    axios: AxiosInstance;
  }
}

// Para ventana global si necesitas hacer peticiones AJAX manuales
window.axios = instance;

export default instance;