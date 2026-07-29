import ProtectedRoute from "./components/auth/ProtectedRoute";
import ArticleListPage from "./pages/ArticleListPage";
import LoginPage from "./pages/LoginPage";
import RegisterPage from "./pages/RegisterPage";
import { Route, Routes } from "react-router-dom";

function App() {
  return (
    <Routes>
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/"
        element={
          <ProtectedRoute>
            <ArticleListPage />
          </ProtectedRoute>
        }
      />
    </Routes>
  );
}
export default App;
