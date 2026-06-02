import { Outlet } from 'react-router-dom';

export function AuthLayout() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background">
      <div className="w-full max-w-md space-y-8 rounded-lg border bg-card p-8 shadow-sm">
        <div className="text-center">
          <h1 className="text-2xl font-bold">UZRJ</h1>
          <p className="mt-2 text-sm text-muted-foreground">
            Udruženje Zdravstvenih Radnika
          </p>
        </div>
        <Outlet />
      </div>
    </div>
  );
}
