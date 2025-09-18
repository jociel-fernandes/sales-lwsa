export function todayStr(): string {
  const d = new Date()
  const yyyy = d.getFullYear()
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const dd = String(d.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

export function normalizeDateForInput(d: string | Date | null | undefined): string {
  if (!d) return ''
  if (d instanceof Date) return isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 10)
  const s = String(d)
  const isoDateOnly = /^\d{4}-\d{2}-\d{2}$/.test(s)
  if (isoDateOnly) return s
  const hasSpace = s.includes(' ')
  const dt = new Date(hasSpace ? s.replace(' ', 'T') : s)
  return isNaN(dt.getTime()) ? s.slice(0, 10) : dt.toISOString().slice(0, 10)
}

export function formatDate(d?: string): string {
  if (!d) return '—'
  const isoDateOnly = /^\d{4}-\d{2}-\d{2}$/.test(d)
  if (isoDateOnly) {
    const [y, m, day] = d.split('-')
    return `${day.padStart(2, '0')}/${m.padStart(2, '0')}/${y}`
  }
  const isoUtcMidnight = /^\d{4}-\d{2}-\d{2}T00:00:00(?:\.\d+)?Z$/.test(d)
  if (isoUtcMidnight) {
    const [y, m, day] = d.split('T')[0].split('-')
    return `${day.padStart(2, '0')}/${m.padStart(2, '0')}/${y}`
  }
  const parsed = new Date(d)
  if (isNaN(parsed.getTime())) return d
  const day = String(parsed.getDate()).padStart(2, '0')
  const month = String(parsed.getMonth() + 1).padStart(2, '0')
  const year = parsed.getFullYear()
  return `${day}/${month}/${year}`
}

export function formatCurrency(v?: number): string {
  if (v === undefined || v === null) return '—'
  try {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(v))
  } catch {
    return String(v)
  }
}

export function isFutureDate(dateStr: string): boolean {
  if (!dateStr) return false
  try {
    const selected = new Date(dateStr + 'T00:00:00')
    const max = new Date(todayStr() + 'T23:59:59')
    return selected.getTime() > max.getTime()
  } catch {
    return false
  }
}
