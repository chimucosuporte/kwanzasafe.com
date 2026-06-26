/**
 * Configuração de ambiente da app.
 *
 * A base da API é controlada por `EXPO_PUBLIC_API_URL` (definida em `.env`
 * ou no ambiente de build). Por defeito aponta para produção.
 *
 * Em desenvolvimento local contra o WAMP, define no `.env`:
 *   EXPO_PUBLIC_API_URL=http://192.168.x.x:8000      (IP da máquina na LAN)
 * (o `localhost` do telemóvel não chega ao servidor do PC).
 */
const RAW_BASE = process.env.EXPO_PUBLIC_API_URL ?? 'https://kwanzasafe.com';

/** Base do servidor, sem barra final. */
export const API_BASE_URL = RAW_BASE.replace(/\/+$/, '');

/** Prefixo versionado da API REST. */
export const API_URL = `${API_BASE_URL}/api/v1`;
