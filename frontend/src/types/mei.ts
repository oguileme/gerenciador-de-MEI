/** Contrato de backend/app/Http/Resources/MeiResource.php. */
export interface Mei {
  id: number
  cnpj: string
  mei_name: string
  opening_date: string | null
  ibge_municipality_code: string
  created_at: string | null
  updated_at: string | null
}

export interface CreateMei {
  mei_name: string
  cnpj: string
  opening_date: string
  ibge_municipality_code: string
}

export interface MeiPage {
  data: Mei[]
  meta: { current_page: number; last_page: number }
}
