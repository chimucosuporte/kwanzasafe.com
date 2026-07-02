/**
 * Configuração da app admin. A base da API vem de EXPO_PUBLIC_API_URL
 * (inlined no bundle em build time). Ver .env.example.
 */
const base = process.env.EXPO_PUBLIC_API_URL ?? 'http://10.0.2.2:8000';

export const API_BASE_URL = base.replace(/\/$/, '');
export const API_URL = `${API_BASE_URL}/api/v1`;
