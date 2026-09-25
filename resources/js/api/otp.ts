import httpClient from '../utils/http'

export const otpAPI = {
  verify: async (email: string, code: string) => {
    const response = await httpClient.post('/api/otp/verify', { email, code })
    return response.data
  },

  resend: async (email: string) => {
    const response = await httpClient.post('/api/otp/send', { email })
    return response.data
  },
}
