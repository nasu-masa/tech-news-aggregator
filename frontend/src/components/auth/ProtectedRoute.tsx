import type { ReactNode } from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";

type ProtectedRouteProps = {
  children: ReactNode;
};

function ProtectedRoute({ children }: ProtectedRouteProps) {
  const { user, isCheckingAuth, authError } = useAuth();

  if (isCheckingAuth) {
    return <p>確認中...</p>;
  }

  if (authError) {
    return <p>認証状態を確認できませんでした。</p>;
  }

  if (user === null) {
    return <Navigate to="/login" replace />;
  }

  return children;
}

export default ProtectedRoute;
