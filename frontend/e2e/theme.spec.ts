import { expect, test } from '@playwright/test'

test('segue o dispositivo e persiste a escolha entre telas', async ({ page }) => {
  await page.emulateMedia({ colorScheme: 'dark' })
  await page.route('**/api/user', (route) => route.fulfill({
    status: 401, json: { message: 'Não autenticado.' },
  }))
  await page.goto('/')
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark')
  await expect(page.locator('meta[name="theme-color"]')).toHaveAttribute('content', '#101713')
  await expect(page.getByRole('button', { name: 'Ativar modo claro' })).toBeVisible()
  await page.screenshot({ path: '/tmp/mei-landing-dark.png', fullPage: true })

  await page.getByRole('button', { name: 'Ativar modo claro' }).focus()
  await page.keyboard.press('Enter')
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light')
  await expect(page.locator('meta[name="theme-color"]')).toHaveAttribute('content', '#254e3a')
  await page.reload()
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light')

  await page.goto('/login')
  await expect(page.getByRole('button', { name: 'Ativar modo escuro' })).toBeVisible()
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light')
  await page.getByRole('button', { name: 'Ativar modo escuro' }).click()
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark')
  await page.screenshot({ path: '/tmp/mei-auth-dark.png', fullPage: true })
})

test('exibe o tema escuro na seleção de MEI em desktop e celular', async ({ page }) => {
  await page.emulateMedia({ colorScheme: 'dark' })
  await page.route('**/api/user', (route) => route.fulfill({
    json: { data: { id: 1, name: 'Pessoa de Teste', email: 'teste@example.com' } },
  }))
  await page.route('**/api/meis?*', (route) => route.fulfill({
    json: { data: [], meta: { current_page: 1, last_page: 1 } },
  }))
  await page.goto('/meis')
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark')
  await expect(page.getByRole('button', { name: 'Ativar modo claro' })).toBeVisible()
  await page.screenshot({ path: '/tmp/mei-selection-dark.png', fullPage: true })
  await page.setViewportSize({ width: 390, height: 844 })
  await page.screenshot({ path: '/tmp/mei-selection-dark-mobile.png', fullPage: true })
})
