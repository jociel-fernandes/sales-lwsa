import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useSalesFilters = defineStore('salesFilters', () => {
  // Sales filters shared across components: seller, date range and page
  const selectedSellerId = ref<number | null>(null)
  const dateFrom = ref<string | null>(null)
  const dateTo = ref<string | null>(null)
  const page = ref<number>(1)

  function hydrateFromQuery(query: Record<string, unknown>) {
    const sv = query.seller_id
    selectedSellerId.value = sv ? Number(String(sv)) : null
    const df = query.date_from
    dateFrom.value = typeof df === 'string' ? df : null
    const dt = query.date_to
    dateTo.value = typeof dt === 'string' ? dt : null
    const pg = query.page
    page.value = pg ? Math.max(1, Number(String(pg))) : 1
  }

  function toQuery() {
    const out: Record<string, string> = {}
    if (selectedSellerId.value) out.seller_id = String(selectedSellerId.value)
    if (dateFrom.value) out.date_from = dateFrom.value
    if (dateTo.value) out.date_to = dateTo.value
    out.page = String(page.value || 1)
    // remove empty values (page always present)
    Object.keys(out).forEach(k => { if (!out[k]) delete out[k] })
    return out
  }

  function clear() {
    selectedSellerId.value = null
    dateFrom.value = null
    dateTo.value = null
    page.value = 1
  }

  return { selectedSellerId, dateFrom, dateTo, page, hydrateFromQuery, toQuery, clear }
})
