export type HomeCategory = {
  id: number
  name: string
  listings_count: number
  icon: string
  color: string
}

export type HomeListingCard = {
  id: number
  title: string
  description: string
  price: number | string
  location: string
  image: string | null
  category: { name: string } | null
  rating?: number
  reviews_count?: number
  is_favorited?: boolean
}

export type HomePayload = {
  parent_categories: HomeCategory[]
  grid_listings: HomeListingCard[]
  vip_listings: HomeListingCard[]
}
