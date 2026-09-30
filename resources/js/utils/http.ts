import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8001'
const GO_API_URL = import.meta.env.VITE_GO_API_URL || 'http://localhost:8080'

export const httpClient = axios.create({
  baseURL: API_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

export const goClient = axios.create({
  baseURL: GO_API_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Add auth token to requests (token Sanctum `web-token` yang diterbitkan saat
// login/OTP — untuk user API; user web-session tetap jalan via cookie karena
// same-origin ke :8001)
export const TOKEN_KEY = 'auth_token'
export const RETURN_TO_KEY = 'postLoginReturnTo'

export function getWebToken(): string | null {
  try {
    return localStorage.getItem(TOKEN_KEY)
  } catch {
    return null
  }
}

export function setWebToken(token: string | null) {
  try {
    if (token) localStorage.setItem(TOKEN_KEY, token)
    else localStorage.removeItem(TOKEN_KEY)
  } catch { /* abaikan (SSR/private mode) */ }
}

export function saveReturnTo(url?: string) {
  try {
    sessionStorage.setItem(RETURN_TO_KEY, url ?? (window.location.pathname + window.location.search))
  } catch { /* abaikan */ }
}

export function takeReturnTo(): string | null {
  try {
    const v = sessionStorage.getItem(RETURN_TO_KEY)
    if (v) sessionStorage.removeItem(RETURN_TO_KEY)
    return v
  } catch {
    return null
  }
}

function attachToken(config: any) {
  const token = getWebToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}

httpClient.interceptors.request.use(attachToken)

// goClient ke :8080 (cross-origin) — kirim token kalau ada, tapi jangan redirect
// otomatis supaya error lock bisa ditampilkan ramah di UI (lihat useSeatLock).
goClient.interceptors.request.use(attachToken)

function isApiRequest(url?: string): boolean {
  return !!url && (url.startsWith('/api/') || url.includes('/api/'))
}

// Handle errors.
//
// - Request /api/* (Bearer token): 401 berarti token tidak valid/kedaluwarsa.
//   Coba silent re-mint SEKALI via /web/auth-token (session cookie, same-origin)
//   lalu ulangi request. Kalau session juga mati -> tolak error apa adanya
//   (halaman menampilkan pesan "sesi habis") — JANGAN redirect buta ke /login,
//   karena user yang session-nya masih hidup akan terpental ke /dashboard
//   oleh middleware `guest` dan kehilangan konteks booking.
// - Request web biasa (mis. /payment-methods via session): pertahankan
//   perilaku lama (redirect /login) karena 401 di sana = session mati.
async function refreshWebToken(): Promise<string | null> {
  try {
    const res = await httpClient.get('/web/auth-token')
    const token = res.data?.token ?? null
    if (token) setWebToken(token)
    return token
  } catch {
    return null
  }
}

httpClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status
    const original: any = error.config ?? {}

    if (status === 401 && isApiRequest(original.url) && !original._tokenRetried) {
      original._tokenRetried = true
      setWebToken(null)
      const fresh = await refreshWebToken()
      if (fresh) {
        original.headers = { ...(original.headers ?? {}), Authorization: `Bearer ${fresh}` }
        return httpClient.request(original)
      }
      // Session ikut mati -> biarkan halaman yang menangani (pesan + login
      // modal di tempat). Simpan returnTo agar habis login bisa kembali.
      saveReturnTo()
      return Promise.reject(error)
    }

    if (status === 401 && !isApiRequest(original.url)) {
      setWebToken(null)
      saveReturnTo()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default httpClient
