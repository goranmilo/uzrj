import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuthStore } from '@/stores/authStore';
import api from '@/services/api';
import type { TokenResponse, User } from '@/types';

const loginSchema = z.object({
  email: z.string().email('Neispravna email adresa'),
  password: z.string().min(1, 'Lozinka je obavezna'),
});

type LoginForm = z.infer<typeof loginSchema>;

export function LoginPage() {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [show2FA, setShow2FA] = useState(false);
  const [tempCredentials, setTempCredentials] = useState<LoginForm | null>(null);
  const [totpCode, setTotpCode] = useState('');
  const navigate = useNavigate();
  const { login } = useAuthStore();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginForm) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post<TokenResponse>('/auth/login', data);
      const { access_token, refresh_token } = response.data;

      // Get user info
      const userResponse = await api.get<User>('/users/me', {
        headers: { Authorization: `Bearer ${access_token}` },
      });

      login(userResponse.data, access_token, refresh_token);
      navigate('/');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      if (error.response?.data?.detail === '2FA required') {
        setTempCredentials(data);
        setShow2FA(true);
      } else {
        setError(error.response?.data?.detail || 'Greška pri prijavi');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handle2FA = async () => {
    if (!tempCredentials || !totpCode) return;

    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post<TokenResponse>('/auth/login', {
        ...tempCredentials,
        totp_code: totpCode,
      });

      const { access_token, refresh_token } = response.data;
      const userResponse = await api.get<User>('/users/me', {
        headers: { Authorization: `Bearer ${access_token}` },
      });

      login(userResponse.data, access_token, refresh_token);
      navigate('/');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      setError(error.response?.data?.detail || 'Neispravan 2FA kod');
    } finally {
      setIsLoading(false);
    }
  };

  if (show2FA) {
    return (
      <div className="space-y-4">
        <h2 className="text-lg font-semibold">Dvofaktorska autentikacija</h2>
        <p className="text-sm text-muted-foreground">
          Unesite kod iz Google Authenticator aplikacije.
        </p>
        {error && (
          <p className="text-sm text-destructive">{error}</p>
        )}
        <div className="space-y-2">
          <label className="text-sm font-medium">2FA Kod</label>
          <input
            type="text"
            value={totpCode}
            onChange={(e) => setTotpCode(e.target.value)}
            placeholder="000000"
            maxLength={6}
            className="w-full rounded-md border bg-background px-3 py-2 text-center text-lg tracking-widest"
          />
        </div>
        <button
          onClick={handle2FA}
          disabled={isLoading || totpCode.length !== 6}
          className="w-full rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
        >
          {isLoading ? 'Provera...' : 'Potvrdi'}
        </button>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      {error && (
        <p className="text-sm text-destructive">{error}</p>
      )}

      <div className="space-y-2">
        <label className="text-sm font-medium">Email</label>
        <input
          {...register('email')}
          type="email"
          placeholder="vas@email.com"
          className="w-full rounded-md border bg-background px-3 py-2"
        />
        {errors.email && (
          <p className="text-sm text-destructive">{errors.email.message}</p>
        )}
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Lozinka</label>
        <input
          {...register('password')}
          type="password"
          placeholder="••••••••"
          className="w-full rounded-md border bg-background px-3 py-2"
        />
        {errors.password && (
          <p className="text-sm text-destructive">{errors.password.message}</p>
        )}
      </div>

      <button
        type="submit"
        disabled={isLoading}
        className="w-full rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
      >
        {isLoading ? 'Prijavljivanje...' : 'Prijavi se'}
      </button>
    </form>
  );
}
