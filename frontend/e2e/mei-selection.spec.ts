import { expect, test } from '@playwright/test'

test('seleciona MEI e mantém o contexto ao recarregar', async ({ page }) => {
  const meis = [
    { id: 1, mei_name: 'Ateliê Aurora', cnpj: '00000000000100' },
    { id: 2, mei_name: 'Estúdio Horizonte', cnpj: '00000000000200' },
    { id: 3, mei_name: 'Café da Vila', cnpj: '00000000000300' },
  ]
  await page.route('**/api/user', (route) => route.fulfill({ json: {
    data: { id: 1, name: 'Pessoa de Teste', email: 'teste@example.com' },
  } }))
  await page.route('**/api/meis?*', (route) => route.fulfill({ json: {
    data: meis, meta: { current_page: 1, last_page: 1 },
  } }))
  await page.route('**/api/meis/2', (route) => route.fulfill({ json: { data: meis[1] } }))
  await page.goto('/meis')
  await expect(page.getByRole('heading', { name: 'Qual MEI você quer acessar?' })).toBeVisible()
  await expect(page.getByRole('link', { name: /Ateliê Aurora/ })).toBeVisible()
  await page.screenshot({ path: '/tmp/mei-selection-desktop.png', fullPage: true })
  await page.getByRole('button', { name: /Adicionar MEI/ }).click()
  await expect(page.getByRole('dialog')).toBeVisible()
  await expect(page.getByLabel('Nome do MEI')).toBeFocused()
  await page.keyboard.press('Escape')
  await expect(page.getByRole('button', { name: /Adicionar MEI/ })).toBeFocused()
  await page.setViewportSize({ width: 375, height: 812 })
  await page.screenshot({ path: '/tmp/mei-selection-mobile.png', fullPage: true })
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true)
  await page.getByRole('link', { name: /Estúdio Horizonte/ }).click()
  await expect(page).toHaveURL(/\/meis\/2\/dashboard$/)
  await page.reload()
  await expect(page.getByRole('heading', { name: 'Visão geral' })).toBeVisible()
  await page.getByRole('link', { name: 'Trocar MEI' }).click()
  await expect(page).toHaveURL(/\/meis$/)
})
