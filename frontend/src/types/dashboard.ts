/** Contrato do DashboardResource. Cálculos fiscais pertencem exclusivamente à API. */
export interface DashboardMetrics {
  year: number
  as_of: string
  date_basis: 'issue_date'
  annual_revenue: string
  active_invoice_count: number
  months: { month: number; amount: string; height_percent: string }[]
  monthly_average: {
    amount: string | null
    height_percent: string | null
    month_count: number
  }
  ceiling: {
    amount: string
    used_percent: string
    remaining_amount: string
    status: 'safe' | 'warning' | 'danger'
    warning_percent: number
    danger_percent: number
  }
  projection: {
    amount: string | null
    status: 'insufficient_data' | 'within_limit' | 'over_limit' | 'closed_year'
    completed_months: number | null
  }
}
