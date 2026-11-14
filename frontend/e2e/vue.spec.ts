import { test, expect } from '@playwright/test'

test('abre a landing page e navega entre as telas de autenticação', async ({ page }) => {
  await page.goto('/')
  await expect(page.getByRole('heading', { level: 1 })).toContainText('Seu negócio em dia.')
  await page.getByRole('navigation', { name: 'Acesso à conta' })
    .getByRole('link', { name: 'Entrar', exact: true }).click()
  await expect(page).toHaveURL(/\/login$/)
  await expect(page.getByRole('heading', { level: 1 })).toHaveText('Boas-vindas de volta.')
  await page.getByRole('link', { name: 'Criar conta', exact: true }).click()
  await expect(page).toHaveURL(/\/register$/)
  await expect(page.getByLabel('Seu nome')).toBeVisible()
  await page.getByRole('link', { name: 'Entrar', exact: true }).click()
  await expect(page).toHaveTitle('Entrar | Meu MEI')
  await page.getByRole('link', { name: 'Meu MEI, página inicial' }).click()
  await expect(page).toHaveTitle('Meu MEI | Seu negócio em dia')
  await expect(page.getByRole('heading', { level: 1 })).toContainText('Seu negócio em dia.')
})

test('os convites da landing page levam ao cadastro no topo da tela', async ({ page }) => {
  for (const region of ['.site-header', '.hero', '.closing']) {
    await page.goto('/')
    await page.locator(region).getByRole('link', { name: /Criar.*conta/ }).click()
    await expect(page).toHaveURL(/\/register$/)
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Crie sua conta.')
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0)
  }
})

test('navega pelas seções e abre dúvidas pelo teclado', async ({ page }) => {
  await page.goto('/')
  await page.getByRole('navigation', { name: 'Seções da página' })
    .getByRole('link', { name: 'Dúvidas', exact: true }).click()
  await expect(page).toHaveURL(/#duvidas$/)
  const question = page.locator('summary').filter({ hasText: 'O Meu MEI já está disponível' })
  const answer = page.getByText('Estamos construindo o Meu MEI.', { exact: false })
  await expect(answer).toBeHidden()
  await question.focus()
  await page.keyboard.press('Enter')
  await expect(answer).toBeVisible()
  await expect(answer).toContainText('ainda não estão disponíveis nesta prévia')
  await page.keyboard.press('Space')
  await expect(answer).toBeHidden()
})

test('mantém a landing page e os links de acesso utilizáveis no celular', async ({ page }) => {
  await page.setViewportSize({ width: 320, height: 740 })
  await page.goto('/')
  const accountNav = page.getByRole('navigation', { name: 'Acesso à conta' })
  await expect(accountNav.getByRole('link', { name: 'Entrar', exact: true })).toBeVisible()
  await expect(accountNav.getByRole('link', { name: 'Criar conta', exact: true })).toBeVisible()
  expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320)
  await page.locator('.hero').getByRole('link', { name: 'Criar minha conta' }).click()
  await expect(page).toHaveURL(/\/register$/)
})

test('valida o cadastro e mantém o envio inteiramente local', async ({ page }) => {
  const requests: string[] = []
  page.on('request', (request) => {
    if (['fetch', 'xhr'].includes(request.resourceType()) || request.method() === 'POST') {
      requests.push(request.url())
    }
  })
  await page.goto('/register')
  await page.getByRole('button', { name: 'Criar minha conta' }).click()
  await expect(page.getByLabel('Seu nome')).toBeFocused()
  await page.getByLabel('Seu nome').fill('Pessoa de Teste')
  await page.getByLabel('E-mail', { exact: true }).fill('pessoa@example.com')
  await page.getByLabel('Senha', { exact: true }).fill('senha-ficticia')
  await page.getByLabel('Confirme sua senha', { exact: true }).fill('senha-diferente')
  await page.getByRole('button', { name: 'Criar minha conta' }).click()
  await expect(page.getByText('As senhas não coincidem.')).toBeVisible()
  await page.getByLabel('Confirme sua senha', { exact: true }).fill('senha-ficticia')
  await page.getByRole('button', { name: 'Criar minha conta' }).click()
  await expect(page.getByRole('status')).toContainText('nenhuma conta foi criada')
  await expect(page).toHaveURL(/\/register$/)
  expect(requests).toEqual([])
})

test('permite preencher e enviar o login pelo teclado', async ({ page }) => {
  await page.goto('/login')
  await page.getByLabel('E-mail', { exact: true }).focus()
  await page.keyboard.type('pessoa@example.com')
  await page.keyboard.press('Tab')
  await expect(page.getByLabel('Senha', { exact: true })).toBeFocused()
  await page.keyboard.type('senha-ficticia')
  await page.keyboard.press('Tab')
  await expect(page.getByRole('button', { name: 'Mostrar senha', exact: true })).toBeFocused()
  await page.keyboard.press('Space')
  await expect(page.getByLabel('Senha', { exact: true })).toHaveAttribute('type', 'text')
  await page.keyboard.press('Tab')
  await page.keyboard.press('Enter')
  await expect(page.getByRole('status')).toContainText('o acesso ainda não está disponível')
})

test('mantém os formulários acessíveis sem rolagem horizontal no celular', async ({ page }) => {
  await page.setViewportSize({ width: 360, height: 800 })
  for (const route of ['/login', '/register']) {
    await page.goto(route)
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
    await expect(page.getByRole('button', { name: /Entrar na minha conta|Criar minha conta/ })).toBeVisible()
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(360)
  }
})
