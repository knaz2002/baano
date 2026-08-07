export type ListingImage = {
  id: number
  url: string | null
}

export type ListingDetail = {
  id: number
  title: string
  description: string | null
  price: number | string
  price_type: string | null
  location: string | null
  city: string | null
  custom_attributes: Record<string, unknown>
  images: ListingImage[]
  category: { id: number; name: string } | null
  user_id: number
  user: { id: number; name: string; phone: string | null } | null
  created_at: string | null
  views: number
  is_active: boolean
}

export type SimilarListing = {
  id: number
  title: string
  description: string
  price: number | string
  location: string
  image: string | null
  category: { id: number; name: string } | null
}

export type ListingShowPayload = {
  listing: ListingDetail
  is_favorited: boolean
  similar_listings: SimilarListing[]
}
