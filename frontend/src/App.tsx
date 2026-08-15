import ProtectedRoute from "./components/auth/ProtectedRoute";
import VerifiedRoute from "./components/auth/VerifiedRoute";
import AppLayout from "./components/layout/AppLayout";
import ArticleDetailPage from "./pages/ArticleDetailPage.tsx";
import ArticleListPage from "./pages/ArticleListPage";
import LoginPage from "./pages/LoginPage";
import RegisterPage from "./pages/RegisterPage";
import VerifyEmailPage from "./pages/VerifyEmailPage.tsx";
import { Route, Routes } from "react-router-dom";

function App() {
  return (
    <Routes>
      <Route element={<AppLayout />}>
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route
          path="/verify-email"
          element={
            <ProtectedRoute>
              <VerifyEmailPage />
            </ProtectedRoute>
          }
        />
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
      </Route>
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
    </Routes>
  );
}
export default App;
