import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MainLayout } from '@/layouts/MainLayout';
import { AuthLayout } from '@/layouts/AuthLayout';
import { ProtectedRoute } from '@/layouts/ProtectedRoute';
import { LoginPage } from '@/pages/Login';
import { DashboardPage } from '@/pages/Dashboard';
import { MembersPage } from '@/pages/Members';
import { MemberDetailPage } from '@/pages/MemberDetail';
import { ClanarinePage } from '@/pages/Clanarine';
import { EdukacijePage } from '@/pages/Edukacije';
import { DocumentsPage } from '@/pages/Documents';
import { NotificationsPage } from '@/pages/Notifications';
import { AdminPage } from '@/pages/Admin';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          {/* Auth routes */}
          <Route element={<AuthLayout />}>
            <Route path="/login" element={<LoginPage />} />
          </Route>

          {/* Protected routes */}
          <Route element={<ProtectedRoute />}>
            <Route element={<MainLayout />}>
              <Route path="/" element={<DashboardPage />} />
              <Route path="/members" element={<MembersPage />} />
              <Route path="/members/new" element={<MemberDetailPage />} />
              <Route path="/members/:id" element={<MemberDetailPage />} />
              <Route path="/members/:id/edit" element={<MemberDetailPage />} />
              <Route path="/clanarine" element={<ClanarinePage />} />
              <Route path="/edukacije" element={<EdukacijePage />} />
              <Route path="/documents" element={<DocumentsPage />} />
              <Route path="/notifications" element={<NotificationsPage />} />
              <Route path="/admin" element={<AdminPage />} />
            </Route>
          </Route>
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}

export default App;
