import ProtectedRoute from "./components/auth/ProtectedRoute";
import VerifiedRoute from "./components/auth/VerifiedRoute";
import AppLayout from "./components/layout/AppLayout";
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
    </Routes>
  );
}
export default App;
