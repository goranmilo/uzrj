export type Role = 'super_admin' | 'admin' | 'moderator' | 'clan' | 'guest';

export interface User {
  id: number;
  email: string;
  role: Role;
  is_active: boolean;
  two_factor_enabled: boolean;
  created_at: string;
}

export interface Member {
  id: number;
  user_id?: number;
  ime: string;
  prezime: string;
  jmbg?: string;
  email?: string;
  telefon?: string;
  adresa?: string;
  datum_rodjenja?: string;
  strucna_sprema_id?: number;
  radno_mesto_id?: number;
  odeljenje_id?: number;
  datum_uclanjenja: string;
  status: 'aktivan' | 'neaktivan' | 'suspendovan';
  created_at: string;
  updated_at: string;
}

export interface Odeljenje {
  id: number;
  naziv: string;
  opis?: string;
  is_active: boolean;
}

export interface StrucnaSprema {
  id: number;
  naziv: string;
  nivo?: number;
  is_active: boolean;
}

export interface RadnoMesto {
  id: number;
  naziv: string;
  opis?: string;
  is_active: boolean;
}

export interface TokenResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
}

export interface LoginRequest {
  email: string;
  password: string;
  totp_code?: string;
}

export interface ApiError {
  detail: string;
}
