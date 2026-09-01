import ProtectedRoute from "./components/auth/ProtectedRoute";
import VerifiedRoute from "./components/auth/VerifiedRoute";
import AppLayout from "./components/layout/AppLayout";
import ArticleLayout from "./components/layout/ArticleLayout";
import ArticleDetailPage from "./pages/ArticleDetailPage.tsx";
import ArticleListPage from "./pages/ArticleListPage";
import ContactPage from "./pages/ContactPage";
import DeleteAccountPage from "./pages/DeleteAccountPage";
import ForgotPasswordPage from "./pages/ForgotPasswordPage";
import LoginPage from "./pages/LoginPage";
import RegisterPage from "./pages/RegisterPage";
import ResetPasswordPage from "./pages/ResetPasswordPage";
import SettingsPage from "./pages/SettingsPage";
import VerifyEmailPage from "./pages/VerifyEmailPage.tsx";
import { Route, Routes } from "react-router-dom";

function App() {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />
        <Route path="/reset-password" element={<ResetPasswordPage />} />
        <Route path="/contact" element={<ContactPage />} />
        <Route
          path="/verify-email"
          element={
            <ProtectedRoute>
              <VerifyEmailPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/settings"
          element={
            <ProtectedRoute>
              <SettingsPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/settings/delete-account"
          element={
            <ProtectedRoute>
              <DeleteAccountPage />
            </ProtectedRoute>
          }
        />
        <Route element={<ArticleLayout />}>
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <VerifiedRoute>
                  <ArticleListPage />
                </VerifiedRoute>
              </ProtectedRoute>
            }
          />
          <Route
            path="/articles/:id"
            element={
              <ProtectedRoute>
                <VerifiedRoute>
                  <ArticleDetailPage />
                </VerifiedRoute>
              </ProtectedRoute>
            }
          />
        </Route>
      </Route>
    </Routes>
  );
}
export default App;
