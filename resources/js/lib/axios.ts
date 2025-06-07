import axios from 'axios';

const instance = axios.create({
  baseURL: 'http://127.0.0.1:8000', // Asegúrate que coincida con tu backend
  withCredentials: true, // 🔥 MUY IMPORTANTE para que funcione CSRF
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

export default instance;
