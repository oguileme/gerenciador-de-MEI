import { expect, test } from '@playwright/test'

test('consulta faturamento, importa XML, muda status e salva configurações', async ({ page }) => {
  const mei = { id: 1, mei_name: 'Ateliê Aurora', cnpj: '00000000000100', opening_date: '2024-01-01', ibge_municipality_code: '0000000', annual_ceiling: '81000.00', created_at: null, updated_at: null }
  const amounts = ['4200.00', '5100.00', '4800.00', '6200.00', '5400.00', '6800.00', '5900.00', '7200.00', '6100.00', '7550.00', '9500.00', '0.00']
  const heights = ['44.21', '53.68', '50.53', '65.26', '56.84', '71.58', '62.11', '75.79', '64.21', '79.47', '100.00', '0.00']
  const metrics = {
    year: 2026, as_of: '2026-11-12', date_basis: 'issue_date', annual_revenue: '68750.00', active_invoice_count: 38,
    months: amounts.map((amount, index) => ({ month: index + 1, amount, height_percent: heights[index] })),
    monthly_average: { amount: '5925.00', height_percent: '62.37', month_count: 10 },
    ceiling: { amount: '81000.00', used_percent: '84.88', remaining_amount: '12250.00', status: 'warning', warning_percent: 80, danger_percent: 95 },
    projection: { amount: '71100.00', status: 'within_limit', completed_months: 10 },
  }
  const invoices = [
    { id: 1, invoice_number: '1042', customer_name: 'Estúdio Horizonte', service_amount: '3200.00', issue_date: '2026-11-10' },
    { id: 2, invoice_number: '1041', customer_name: 'Casa Botânica', service_amount: '1800.00', issue_date: '2026-11-06' },
    { id: 3, invoice_number: '1040', customer_name: 'Café da Vila', service_amount: '4500.00', issue_date: '2026-11-02' },
  ].map((invoice) => ({ ...invoice, mei_id: 1, type: 'nfse', status: 'active', access_key: '00000000000000000000000000000000000000000000000000', net_amount: invoice.service_amount, competence_date: invoice.issue_date, description: 'Serviço de criação visual. Dados fictícios para revisão da interface.', created_at: null, updated_at: null }))
  await page.route('**/api/user', (route) => route.fulfill({ json: { data: { id: 1, name: 'Pessoa de Teste', email: 'teste@example.com' } } }))
  await page.route('**/api/meis/1', async (route) => {
    if (route.request().method() === 'PATCH') Object.assign(mei, route.request().postDataJSON())
    await route.fulfill({ json: { data: mei } })
  })
  await page.route('**/api/meis/1/dashboard*', (route) => route.fulfill({ json: { data: metrics } }))
  await page.route('**/api/meis/1/invoices?*', (route) => route.fulfill({ json: { data: invoices, meta: { current_page: 1, last_page: 1, total: 38 } } }))
  await page.route('**/api/meis/1/annual-limit', async (route) => {
    const payload = route.request().postDataJSON()
    expect(payload).toEqual({ year: 2026, amount: '90000.01' })
    metrics.ceiling.amount = payload.amount
    await route.fulfill({ json: { data: metrics } })
  })
  await page.route('**/api/meis/1/invoices/1/status', async (route) => {
    expect(route.request().postDataJSON()).toEqual({ status: 'cancelled' })
    invoices[0]!.status = 'cancelled'
    metrics.annual_revenue = '65550.00'
    await route.fulfill({ json: { data: invoices[0] } })
  })
  await page.route('**/api/meis/1/invoices/import', (route) => route.fulfill({ status: 201, json: { data: invoices[0] } }))
  await page.goto('/meis/1/dashboard')
  await expect(page.getByRole('heading', { name: 'Visão geral' })).toBeVisible()
  await expect(page.locator('.annual-amount')).toHaveText('R$ 68.750,00')
  await page.screenshot({ path: '/tmp/mei-dashboard-desktop.png', fullPage: true })
  await page.getByRole('button', { name: 'Ativar modo escuro' }).click()
  await page.screenshot({ path: '/tmp/mei-dashboard-dark.png', fullPage: true })
  await page.getByRole('button', { name: 'Ativar modo claro' }).click()
  await page.getByRole('button', { name: 'Jan: R$ 4.200,00' }).click()
  await expect(page.getByRole('heading', { name: 'Janeiro de 2026' })).toBeVisible()
  await page.getByRole('button', { name: 'Ver nota' }).first().click()
  await expect(page.getByText('Serviço de criação visual.', { exact: false })).toBeVisible()
  await page.getByRole('button', { name: 'Fechar', exact: true }).click()
  await page.setViewportSize({ width: 390, height: 844 })
  await page.screenshot({ path: '/tmp/mei-dashboard-mobile.png', fullPage: true })
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
  await page.setViewportSize({ width: 1440, height: 1000 })
  await page.getByRole('button', { name: 'Alterar limite anual', exact: true }).click()
  await expect(page.getByRole('dialog')).toBeVisible()
  await page.getByLabel('Limite anual (R$)').fill('90000,01')
  await page.screenshot({ path: '/tmp/mei-dashboard-settings.png', fullPage: true })
  await page.getByRole('button', { name: 'Salvar alterações' }).click()
  await expect(page.getByRole('dialog')).toBeHidden()
  await expect(page.locator('.ceiling-amount')).toHaveText('R$ 90.000,01')
  await page.getByLabel('Status da nota 1042').selectOption('cancelled')
  await expect(page.locator('.annual-amount')).toHaveText('R$ 65.550,00')
  await page.locator('.upload-dropzone').evaluate((element) => {
    const transfer = new DataTransfer()
    transfer.items.add(new File(['<xml/>'], 'exemplo.xml', { type: 'application/xml' }))
    element.dispatchEvent(new DragEvent('drop', {
      bubbles: true, cancelable: true, dataTransfer: transfer,
    }))
  })
  await expect(page.getByText('exemplo.xml: Importada')).toBeVisible()
  await page.getByRole('button', { name: 'Dados do MEI' }).click()
  await page.getByLabel('Nome do MEI', { exact: true }).fill('Ateliê Aurora atualizado')
  await page.getByRole('button', { name: 'Salvar alterações' }).click()
  await expect(page.locator('.business strong')).toHaveText('Ateliê Aurora atualizado')
})
