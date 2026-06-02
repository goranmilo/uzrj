import api from './api';
import type { User, TokenResponse } from '@/types';

interface LoginRequest {
  email: string;
  password: string;
  totp_code?: string;
}

interface RegisterRequest {
  email: string;
  password: string;
  ime: string;
  prezime: string;
}

export const authService = {
  async login(data: LoginRequest): Promise<TokenResponse & { user: User }> {
    const response = await api.post('/auth/login', data);
    return response.data;
  },

  async register(data: RegisterRequest): Promise<TokenResponse & { user: User }> {
    const response = await api.post('/auth/register', data);
    return response.data;
  },

  async refreshToken(refreshToken: string): Promise<TokenResponse> {
    const response = await api.post('/auth/refresh', { refresh_token: refreshToken });
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout');
  },

  async forgotPassword(email: string): Promise<{ message: string }> {
    const response = await api.post('/auth/forgot-password', { email });
    return response.data;
  },

  async resetPassword(token: string, newPassword: string): Promise<{ message: string }> {
    const response = await api.post('/auth/reset-password', {
      token,
      new_password: newPassword,
    });
    return response.data;
  },

  async setup2FA(): Promise<{ secret: string; qr_code: string; otpauth_url: string }> {
    const response = await api.post('/auth/2fa/setup');
    return response.data;
  },

  async verify2FA(code: string): Promise<{ message: string }> {
    const response = await api.post('/auth/2fa/verify', { code });
    return response.data;
  },

  async disable2FA(password: string): Promise<{ message: string }> {
    const response = await api.post('/auth/2fa/disable', { password });
    return response.data;
  },

  async getCurrentUser(): Promise<User> {
    const response = await api.get('/users/me');
    return response.data;
  },
};
