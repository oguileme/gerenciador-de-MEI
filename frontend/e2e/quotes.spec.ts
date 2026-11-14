import { expect, test } from '@playwright/test'

test('prepara orçamento, confere a folha e consulta os serviços', async ({ page }) => {
  const mei = {
    id: 1, mei_name: 'Ateliê Aurora', cnpj: '00000000000100', opening_date: '2024-01-01',
    ibge_municipality_code: '0000000', annual_ceiling: '81000.00', created_at: null, updated_at: null,
  }
  const quote = {
    id: 7, mei_id: 1, issuer_name: 'Ateliê Aurora', recipient_name: 'Casa Botânica',
    issue_date: '2026-11-14', status: 'pending', total_amount: '3650.00',
    created_at: '2026-11-14T12:00:00Z', updated_at: '2026-11-14T12:00:00Z',
  }
  const detailed = {
    ...quote,
    items: [
      { id: 1, description: 'Projeto de identidade visual', unit_price: '2400.00', quantity: '1.000', subtotal: '2400.00' },
      { id: 2, description: 'Aplicações para redes sociais', unit_price: '250.00', quantity: '5.000', subtotal: '1250.00' },
    ],
  }
  const metrics = {
    year: 2026, as_of: '2026-11-14', date_basis: 'issue_date', annual_revenue: '0.00', active_invoice_count: 0,
    months: Array.from({ length: 12 }, (_, index) => ({ month: index + 1, amount: '0.00', height_percent: '0.00' })),
    monthly_average: { amount: '0.00', height_percent: '0.00', month_count: 10 },
    ceiling: { amount: '81000.00', used_percent: '0.00', remaining_amount: '81000.00', status: 'safe', warning_percent: 80, danger_percent: 95 },
    projection: { amount: null, status: 'insufficient_data', completed_months: 0 },
  }

  await page.route('**/api/user', (route) => route.fulfill({ json: { data: { id: 1, name: 'Pessoa de Teste', email: 'teste@example.com' } } }))
  await page.route('**/api/meis/1', (route) => route.fulfill({ json: { data: mei } }))
  await page.route('**/api/meis/1/dashboard*', (route) => route.fulfill({ json: { data: metrics } }))
  await page.route('**/api/meis/1/invoices?*', (route) => route.fulfill({ json: { data: [], meta: { current_page: 1, last_page: 1, total: 0 } } }))
  await page.route('**/api/meis/1/quotes?*', (route) => route.fulfill({ json: { data: [quote], meta: { current_page: 1, last_page: 1, total: 1 } } }))
  await page.route('**/api/meis/1/quotes/7', (route) => route.fulfill({ json: { data: detailed } }))

  await page.goto('/meis/1/dashboard#quotes')
  await expect(page.getByRole('heading', { name: 'Orçamentos' })).toBeVisible()
  await page.screenshot({ path: '/tmp/mei-quotes-ledger.png', fullPage: true })

  await page.getByRole('button', { name: 'Novo orçamento' }).click()
  await page.getByLabel('Quem receberá este orçamento?').fill('Estúdio Horizonte')
  await page.getByLabel('Descrição do serviço 1').fill('Direção de arte mensal')
  await page.getByLabel('Valor unitário').fill('850,00')
  await page.getByLabel('Quantidade').fill('2')
  await expect(page.getByText('R$ 1.700,00', { exact: true })).toHaveCount(2)
  await page.getByRole('button', { name: 'Adicionar serviço' }).click()
  await page.getByLabel('Descrição do serviço 2').fill('Pacote de fotografias')
  await page.getByLabel('Valor unitário').nth(1).fill('120,50')
  await page.getByLabel('Quantidade').nth(1).fill('3')
  await expect(page.getByText('R$ 2.061,50', { exact: true })).toBeVisible()
  await page.screenshot({ path: '/tmp/mei-quote-form-desktop.png', fullPage: true })

  await page.setViewportSize({ width: 390, height: 844 })
  await page.screenshot({ path: '/tmp/mei-quote-form-mobile.png', fullPage: true })
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await page.getByRole('button', { name: 'Cancelar' }).click()

  await page.setViewportSize({ width: 1440, height: 1000 })
  await page.getByRole('button', { name: 'Ver proposta' }).click()
  await expect(page.getByText('Projeto de identidade visual')).toBeVisible()
  await expect(page.getByText('R$ 3.650,00', { exact: true })).toBeVisible()
})
