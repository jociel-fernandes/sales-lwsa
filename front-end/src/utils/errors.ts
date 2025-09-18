export function extractErrorMessage(err: unknown, fallback = 'Erro no Processamento'): string {
  try {
    if (!err || typeof err !== 'object') return fallback
    const anyErr = err as { message?: string; response?: { data?: { message?: string } } }
    return anyErr.response?.data?.message || anyErr.message || fallback
  } catch {
    return fallback
  }
}
