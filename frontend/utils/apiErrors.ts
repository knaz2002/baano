export function parseApiErrors(error: unknown): Record<string, string> {
  const data = (error as { data?: { message?: string; errors?: Record<string, string[] | string> } })?.data

  if (data?.errors && typeof data.errors === 'object') {
    const out: Record<string, string> = {}
    for (const [key, value] of Object.entries(data.errors)) {
      out[key] = Array.isArray(value) ? (value[0] ?? '') : String(value)
    }
    return out
  }

  if (data?.message) {
    return { form: data.message }
  }

  return { form: 'Ошибка запроса' }
}

/** Маска телефона как на текущем Inertia Register */
export function formatRuPhone(raw: string): string {
  let value = raw.replace(/\D/g, '')
  if (value.startsWith('8')) {
    value = '7' + value.slice(1)
  }
  if (!value.startsWith('7')) {
    value = '7' + value
  }

  let formatted = '+7'
  if (value.length > 1) {
    formatted += ' (' + value.slice(1, 4)
  }
  if (value.length >= 4) {
    formatted += ') ' + value.slice(4, 7)
  }
  if (value.length >= 7) {
    formatted += '-' + value.slice(7, 9)
  }
  if (value.length >= 9) {
    formatted += '-' + value.slice(9, 11)
  }

  return formatted
}
