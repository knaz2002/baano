export type CatalogCategory = {
  id: number
  name: string
  children?: CatalogCategory[]
}

export type CatalogListing = {
  id: number
  title: string
  description: string
  price: number | string
  location: string
  city: string
  image: string | null
  category: { id: number; name: string } | null
  user: { id: number; name: string } | null
  rating?: number
  reviews_count?: number
  is_favorited?: boolean
}

export type FilterConfigType = 'commercial' | 'apartments' | 'transport' | 'equipment' | null

export type FilterConfig = {
  type: FilterConfigType
  options: {
    area?: { min: number; max: number }
    rooms?: number[]
    floors?: number[]
    brands?: string[]
    modelsByBrand?: Record<string, string[]>
    years?: number[]
  }
}

export type CatalogFilters = {
  category?: string | number | null
  city?: string | null
  search?: string | null
  sort?: string
  price_min?: string | number | null
  price_max?: string | number | null
  area_min?: string | number | null
  area_max?: string | number | null
  rooms?: string[]
  floor?: string | number | null
  brand?: string | null
  model?: string | null
  year?: string | number | null
}

export type CatalogPayload = {
  listings: CatalogListing[]
  categories: CatalogCategory[]
  cities: string[]
  current_category: { id: number; name: string } | null
  price_range: { min: number; max: number }
  filter_config: FilterConfig
  filters: CatalogFilters
}

export type CatalogMeta = {
  current_page: number
  last_page: number
  per_page: number
  total: number
}
