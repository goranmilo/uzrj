import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useAuthStore } from '@/stores/authStore';
import api from '@/services/api';
import type { User } from '@/types';
import { Loader2, Eye, EyeOff, Shield } from 'lucide-react';

// Validation schemas
const loginSchema = z.object({
  email: z.string().email('Neispravna email adresa'),
  password: z.string().min(1, 'Lozinka je obavezna'),
});

const registerSchema = z.object({
  email: z.string().email('Neispravna email adresa'),
  password: z.string().min(8, 'Lozinka mora imati najmanje 8 karaktera'),
  confirmPassword: z.string(),
  ime: z.string().min(2, 'Ime je obavezno'),
  prezime: z.string().min(2, 'Prezime je obavezno'),
}).refine((data) => data.password === data.confirmPassword, {
  message: 'Lozinke se ne poklapaju',
  path: ['confirmPassword'],
});

const forgotPasswordSchema = z.object({
  email: z.string().email('Neispravna email adresa'),
});

const resetPasswordSchema = z.object({
  token: z.string().min(1, 'Token je obavezan'),
  newPassword: z.string().min(8, 'Lozinka mora imati najmanje 8 karaktera'),
  confirmPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmPassword, {
  message: 'Lozinke se ne poklapaju',
  path: ['confirmPassword'],
});

type LoginForm = z.infer<typeof loginSchema>;
type RegisterForm = z.infer<typeof registerSchema>;
type ForgotPasswordForm = z.infer<typeof forgotPasswordSchema>;
type ResetPasswordForm = z.infer<typeof resetPasswordSchema>;

type AuthView = 'login' | 'register' | 'forgot-password' | 'reset-password' | '2fa';

export function LoginPage() {
  const [view, setView] = useState<AuthView>('login');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);
  const [totpCode, setTotpCode] = useState('');
  const [tempCredentials, setTempCredentials] = useState<LoginForm | null>(null);

  const navigate = useNavigate();
  const { login, isAuthenticated } = useAuthStore();

  // Redirect if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      navigate('/');
    }
  }, [isAuthenticated, navigate]);

  // Login form
  const loginForm = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  // Register form
  const registerForm = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
  });

  // Forgot password form
  const forgotPasswordForm = useForm<ForgotPasswordForm>({
    resolver: zodResolver(forgotPasswordSchema),
  });

  // Reset password form
  const resetPasswordForm = useForm<ResetPasswordForm>({
    resolver: zodResolver(resetPasswordSchema),
  });

  // Handle login
  const onLogin = async (data: LoginForm) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/login', data);
      const { access_token, refresh_token, user } = response.data;

      // Get full user info
      const userResponse = await api.get<User>('/users/me', {
        headers: { Authorization: `Bearer ${access_token}` },
      });

      login(userResponse.data, access_token, refresh_token);
      navigate('/');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      if (error.response?.data?.detail === '2FA required') {
        setTempCredentials(data);
        setView('2fa');
      } else {
        setError(error.response?.data?.detail || 'Greška pri prijavi');
      }
    } finally {
      setIsLoading(false);
    }
  };

  // Handle 2FA verification
  const handle2FA = async () => {
    if (!tempCredentials || !totpCode || totpCode.length !== 6) return;

    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/login', {
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
      setTotpCode('');
    } finally {
      setIsLoading(false);
    }
  };

  // Handle registration
  const onRegister = async (data: RegisterForm) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await api.post('/auth/register', {
        email: data.email,
        password: data.password,
        ime: data.ime,
        prezime: data.prezime,
      });

      const { access_token, refresh_token } = response.data;
      const userResponse = await api.get<User>('/users/me', {
        headers: { Authorization: `Bearer ${access_token}` },
      });

      login(userResponse.data, access_token, refresh_token);
      navigate('/');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      setError(error.response?.data?.detail || 'Greška pri registraciji');
    } finally {
      setIsLoading(false);
    }
  };

  // Handle forgot password
  const onForgotPassword = async (data: ForgotPasswordForm) => {
    setIsLoading(true);
    setError(null);
    setSuccess(null);

    try {
      await api.post('/auth/forgot-password', { email: data.email });
      setSuccess('Ako postoji nalog sa tom email adresom, poslat je link za resetovanje lozinke.');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      setError(error.response?.data?.detail || 'Greška pri slanju zahteva');
    } finally {
      setIsLoading(false);
    }
  };

  // Handle reset password
  const onResetPassword = async (data: ResetPasswordForm) => {
    setIsLoading(true);
    setError(null);
    setSuccess(null);

    try {
      await api.post('/auth/reset-password', {
        token: data.token,
        new_password: data.newPassword,
      });
      setSuccess('Lozinka je uspešno promenjena. Možete se prijaviti.');
      setView('login');
      resetPasswordForm.reset();
    } catch (err: unknown) {
      const error = err as { response?: { data?: { detail?: string } } };
      setError(error.response?.data?.detail || 'Greška pri resetovanju lozinke');
    } finally {
      setIsLoading(false);
    }
  };

  // Render login form
  const renderLogin = () => (
    <form onSubmit={loginForm.handleSubmit(onLogin)} className="space-y-4">
      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      <div className="space-y-2">
        <label className="text-sm font-medium">Email</label>
        <input
          {...loginForm.register('email')}
          type="email"
          placeholder="vas@email.com"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {loginForm.formState.errors.email && (
          <p className="text-sm text-destructive">
            {loginForm.formState.errors.email.message}
          </p>
        )}
      </div>

      <div className="space-y-2">
        <div className="flex items-center justify-between">
          <label className="text-sm font-medium">Lozinka</label>
          <button
            type="button"
            onClick={() => { setView('forgot-password'); setError(null); setSuccess(null); }}
            className="text-xs text-primary hover:underline"
          >
            Zaboravljena lozinka?
          </button>
        </div>
        <div className="relative">
          <input
            {...loginForm.register('password')}
            type={showPassword ? 'text' : 'password'}
            placeholder="••••••••"
            className="w-full rounded-md border bg-background px-3 py-2 pr-10"
            disabled={isLoading}
          />
          <button
            type="button"
            onClick={() => setShowPassword(!showPassword)}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
          >
            {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
          </button>
        </div>
        {loginForm.formState.errors.password && (
          <p className="text-sm text-destructive">
            {loginForm.formState.errors.password.message}
          </p>
        )}
      </div>

      <button
        type="submit"
        disabled={isLoading}
        className="w-full rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
      >
        {isLoading ? (
          <span className="flex items-center justify-center gap-2">
            <Loader2 className="h-4 w-4 animate-spin" />
            Prijavljivanje...
          </span>
        ) : (
          'Prijavi se'
        )}
      </button>

      <p className="text-center text-sm text-muted-foreground">
        Nemate nalog?{' '}
        <button
          type="button"
          onClick={() => { setView('register'); setError(null); setSuccess(null); }}
          className="text-primary hover:underline"
        >
          Registrujte se
        </button>
      </p>
    </form>
  );

  // Render 2FA form
  const render2FA = () => (
    <div className="space-y-4">
      <div className="flex items-center gap-2 text-lg font-semibold">
        <Shield className="h-5 w-5" />
        Dvofaktorska autentikacija
      </div>

      <p className="text-sm text-muted-foreground">
        Unesite 6-cifreni kod iz Google Authenticator aplikacije.
      </p>

      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      <div className="space-y-2">
        <label className="text-sm font-medium">2FA Kod</label>
        <input
          type="text"
          value={totpCode}
          onChange={(e) => setTotpCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
          placeholder="000000"
          maxLength={6}
          className="w-full rounded-md border bg-background px-3 py-2 text-center text-lg tracking-[0.5em] font-mono"
          autoFocus
        />
      </div>

      <div className="flex gap-2">
        <button
          onClick={() => { setView('login'); setError(null); setTotpCode(''); }}
          className="flex-1 rounded-md border px-4 py-2 hover:bg-accent"
        >
          Nazad
        </button>
        <button
          onClick={handle2FA}
          disabled={isLoading || totpCode.length !== 6}
          className="flex-1 rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
        >
          {isLoading ? (
            <span className="flex items-center justify-center gap-2">
              <Loader2 className="h-4 w-4 animate-spin" />
              Provera...
            </span>
          ) : (
            'Potvrdi'
          )}
        </button>
      </div>
    </div>
  );

  // Render register form
  const renderRegister = () => (
    <form onSubmit={registerForm.handleSubmit(onRegister)} className="space-y-4">
      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-2">
          <label className="text-sm font-medium">Ime</label>
          <input
            {...registerForm.register('ime')}
            type="text"
            placeholder="Ime"
            className="w-full rounded-md border bg-background px-3 py-2"
            disabled={isLoading}
          />
          {registerForm.formState.errors.ime && (
            <p className="text-sm text-destructive">
              {registerForm.formState.errors.ime.message}
            </p>
          )}
        </div>

        <div className="space-y-2">
          <label className="text-sm font-medium">Prezime</label>
          <input
            {...registerForm.register('prezime')}
            type="text"
            placeholder="Prezime"
            className="w-full rounded-md border bg-background px-3 py-2"
            disabled={isLoading}
          />
          {registerForm.formState.errors.prezime && (
            <p className="text-sm text-destructive">
              {registerForm.formState.errors.prezime.message}
            </p>
          )}
        </div>
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Email</label>
        <input
          {...registerForm.register('email')}
          type="email"
          placeholder="vas@email.com"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {registerForm.formState.errors.email && (
          <p className="text-sm text-destructive">
            {registerForm.formState.errors.email.message}
          </p>
        )}
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Lozinka</label>
        <div className="relative">
          <input
            {...registerForm.register('password')}
            type={showPassword ? 'text' : 'password'}
            placeholder="••••••••"
            className="w-full rounded-md border bg-background px-3 py-2 pr-10"
            disabled={isLoading}
          />
          <button
            type="button"
            onClick={() => setShowPassword(!showPassword)}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
          >
            {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
          </button>
        </div>
        {registerForm.formState.errors.password && (
          <p className="text-sm text-destructive">
            {registerForm.formState.errors.password.message}
          </p>
        )}
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Potvrdi lozinku</label>
        <input
          {...registerForm.register('confirmPassword')}
          type={showPassword ? 'text' : 'password'}
          placeholder="••••••••"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {registerForm.formState.errors.confirmPassword && (
          <p className="text-sm text-destructive">
            {registerForm.formState.errors.confirmPassword.message}
          </p>
        )}
      </div>

      <button
        type="submit"
        disabled={isLoading}
        className="w-full rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
      >
        {isLoading ? (
          <span className="flex items-center justify-center gap-2">
            <Loader2 className="h-4 w-4 animate-spin" />
            Registracija...
          </span>
        ) : (
          'Registruj se'
        )}
      </button>

      <p className="text-center text-sm text-muted-foreground">
        Već imate nalog?{' '}
        <button
          type="button"
          onClick={() => { setView('login'); setError(null); setSuccess(null); }}
          className="text-primary hover:underline"
        >
          Prijavite se
        </button>
      </p>
    </form>
  );

  // Render forgot password form
  const renderForgotPassword = () => (
    <form onSubmit={forgotPasswordForm.handleSubmit(onForgotPassword)} className="space-y-4">
      <h2 className="text-lg font-semibold">Zaboravljena lozinka</h2>

      <p className="text-sm text-muted-foreground">
        Unesite vašu email adresu i poslaćemo vam link za resetovanje lozinke.
      </p>

      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      {success && (
        <div className="rounded-md bg-green-500/10 p-3 text-sm text-green-600">
          {success}
        </div>
      )}

      <div className="space-y-2">
        <label className="text-sm font-medium">Email</label>
        <input
          {...forgotPasswordForm.register('email')}
          type="email"
          placeholder="vas@email.com"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {forgotPasswordForm.formState.errors.email && (
          <p className="text-sm text-destructive">
            {forgotPasswordForm.formState.errors.email.message}
          </p>
        )}
      </div>

      <div className="flex gap-2">
        <button
          type="button"
          onClick={() => { setView('login'); setError(null); setSuccess(null); }}
          className="flex-1 rounded-md border px-4 py-2 hover:bg-accent"
        >
          Nazad
        </button>
        <button
          type="submit"
          disabled={isLoading}
          className="flex-1 rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
        >
          {isLoading ? (
            <span className="flex items-center justify-center gap-2">
              <Loader2 className="h-4 w-4 animate-spin" />
              Slanje...
            </span>
          ) : (
            'Pošalji'
          )}
        </button>
      </div>
    </form>
  );

  // Render reset password form
  const renderResetPassword = () => (
    <form onSubmit={resetPasswordForm.handleSubmit(onResetPassword)} className="space-y-4">
      <h2 className="text-lg font-semibold">Resetovanje lozinke</h2>

      <p className="text-sm text-muted-foreground">
        Unesite token iz emaila i novu lozinku.
      </p>

      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {error}
        </div>
      )}

      {success && (
        <div className="rounded-md bg-green-500/10 p-3 text-sm text-green-600">
          {success}
        </div>
      )}

      <div className="space-y-2">
        <label className="text-sm font-medium">Token</label>
        <input
          {...resetPasswordForm.register('token')}
          type="text"
          placeholder="Unesite token iz emaila"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {resetPasswordForm.formState.errors.token && (
          <p className="text-sm text-destructive">
            {resetPasswordForm.formState.errors.token.message}
          </p>
        )}
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Nova lozinka</label>
        <div className="relative">
          <input
            {...resetPasswordForm.register('newPassword')}
            type={showPassword ? 'text' : 'password'}
            placeholder="••••••••"
            className="w-full rounded-md border bg-background px-3 py-2 pr-10"
            disabled={isLoading}
          />
          <button
            type="button"
            onClick={() => setShowPassword(!showPassword)}
            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
          >
            {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
          </button>
        </div>
        {resetPasswordForm.formState.errors.newPassword && (
          <p className="text-sm text-destructive">
            {resetPasswordForm.formState.errors.newPassword.message}
          </p>
        )}
      </div>

      <div className="space-y-2">
        <label className="text-sm font-medium">Potvrdi novu lozinku</label>
        <input
          {...resetPasswordForm.register('confirmPassword')}
          type={showPassword ? 'text' : 'password'}
          placeholder="••••••••"
          className="w-full rounded-md border bg-background px-3 py-2"
          disabled={isLoading}
        />
        {resetPasswordForm.formState.errors.confirmPassword && (
          <p className="text-sm text-destructive">
            {resetPasswordForm.formState.errors.confirmPassword.message}
          </p>
        )}
      </div>

      <div className="flex gap-2">
        <button
          type="button"
          onClick={() => { setView('login'); setError(null); setSuccess(null); }}
          className="flex-1 rounded-md border px-4 py-2 hover:bg-accent"
        >
          Nazad
        </button>
        <button
          type="submit"
          disabled={isLoading}
          className="flex-1 rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
        >
          {isLoading ? (
            <span className="flex items-center justify-center gap-2">
              <Loader2 className="h-4 w-4 animate-spin" />
              Resetovanje...
            </span>
          ) : (
            'Resetuj lozinku'
          )}
        </button>
      </div>
    </form>
  );

  return (
    <div className="w-full max-w-md space-y-6">
      <div className="text-center">
        <h1 className="text-3xl font-bold">UZRJ</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Udruženje Zdravstvenih Radnika
        </p>
      </div>

      <div className="rounded-lg border bg-card p-6 shadow-sm">
        {view === 'login' && renderLogin()}
        {view === '2fa' && render2FA()}
        {view === 'register' && renderRegister()}
        {view === 'forgot-password' && renderForgotPassword()}
        {view === 'reset-password' && renderResetPassword()}
      </div>
    </div>
  );
}
