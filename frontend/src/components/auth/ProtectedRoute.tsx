import axios from "axios";
import { useEffect, useState, type ReactNode } from "react";
import { Navigate } from "react-router-dom";
import { getCurrentUser } from "../../lib/auth";

type ProtectedRouteProps = {
  children: ReactNode;
};

type AuthStatus = "checking" | "authenticated" | "unauthenticated" | "error";

function ProtectedRoute({ children }: ProtectedRouteProps) {
  const [authStatus, setAuthStatus] = useState<AuthStatus>("checking");

  useEffect(() => {
    const checkAuthentication = async () => {
      try {
        await getCurrentUser();
        setAuthStatus("authenticated");
      } catch (error) {
        if (
          axios.isAxiosError(error) &&
          [401, 419].includes(error.response?.status ?? 0)
        ) {
          setAuthStatus("unauthenticated");
          return;
        }

        setAuthStatus("error");
      }
    };

    void checkAuthentication();
  }, []);

  if (authStatus === "checking") {
    return <p>確認中...</p>;
  }

  if (authStatus === "unauthenticated") {
    return <Navigate to="/login" replace />;
  }

  if (authStatus === "error") {
    return <p>認証状態を確認できませんでした。</p>;
  }

  return children;
}

export default ProtectedRoute;
