/** Modos das telas de autenticação, ainda sem integração com a API. */
export type AuthMode = 'login' | 'register'

/** Campos locais; a confirmação de senha pertence apenas à interface nesta etapa. */
export interface AuthFormValues {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type AuthFieldName = keyof AuthFormValues
