import { QueryClient } from '@tanstack/react-query';

/**
 * Cliente TanStack Query — fonte de verdade de todo o estado de servidor
 * (cache, retries, refetch, polling do chat nas fases seguintes).
 */
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
      refetchOnWindowFocus: false,
    },
  },
});
