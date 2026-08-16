export type DadataSuggestion = {
  value: string
  data?: {
    city?: string | null
    settlement?: string | null
    area?: string | null
    city_with_type?: string | null
    region_with_type?: string | null
  }
}

function extractCity(suggestion: DadataSuggestion): string {
  const data = suggestion.data
  if (!data) {
    return ''
  }
  return data.city || data.settlement || data.area || ''
}

/**
 * Подсказки адреса через DaData (как в Inertia Create).
 * Токен: NUXT_PUBLIC_DADATA_TOKEN (или runtimeConfig.public.dadataToken).
 */
export function useDadataAddress(options: {
  location: Ref<string> | WritableComputedRef<string>
  city: Ref<string> | WritableComputedRef<string>
}) {
  const config = useRuntimeConfig()
  const locationQuery = ref(options.location.value || '')
  const suggestions = ref<DadataSuggestion[]>([])
  const showSuggestions = ref(false)
  let timeout: ReturnType<typeof setTimeout> | null = null

  watch(
    () => options.location.value,
    (value) => {
      if (value !== locationQuery.value) {
        locationQuery.value = value || ''
      }
    },
  )

  function onLocationInput() {
    options.city.value = ''
    options.location.value = locationQuery.value

    if (timeout) {
      clearTimeout(timeout)
    }

    if (locationQuery.value.length < 3) {
      suggestions.value = []
      return
    }

    timeout = setTimeout(async () => {
      const token = (config.public.dadataToken as string) || ''
      if (!token) {
        return
      }

      try {
        const response = await fetch(
          'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Accept: 'application/json',
              Authorization: `Token ${token}`,
            },
            body: JSON.stringify({ query: locationQuery.value, count: 5 }),
          },
        )
        const data = await response.json()
        suggestions.value = data.suggestions || []
        showSuggestions.value = suggestions.value.length > 0
      } catch (e) {
        console.error('DaData error:', e)
      }
    }, 300)
  }

  function selectSuggestion(suggestion: DadataSuggestion) {
    locationQuery.value = suggestion.value
    options.location.value = suggestion.value
    options.city.value = extractCity(suggestion)
    suggestions.value = []
    showSuggestions.value = false
  }

  function closeSuggestions() {
    setTimeout(() => {
      showSuggestions.value = false
    }, 150)
  }

  return {
    locationQuery,
    suggestions,
    showSuggestions,
    onLocationInput,
    selectSuggestion,
    closeSuggestions,
  }
}
