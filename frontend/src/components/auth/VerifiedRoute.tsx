import type { ReactNode } from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";

type VerifiedRouteProps = {
  children: ReactNode;
};

function VerifiedRoute({ children }: VerifiedRouteProps) {
  const { user } = useAuth();

  if (user === null) {
    return <Navigate to="/login" replace />;
  }

  if (user.email_verified_at === null) {
    return <Navigate to="/verify-email" replace />;
  }

  return children;
}

export default VerifiedRoute;
