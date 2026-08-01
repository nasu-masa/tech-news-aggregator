import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { logout } from "../lib/auth";

function LogoutButton({ className }: { className?: string }) {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const navigate = useNavigate();
  const { clearUser } = useAuth();

  const handleLogout = async () => {
    if (isSubmitting) return;

    setIsSubmitting(true);

    try {
      await logout();
      clearUser();

      navigate("/login", { replace: true });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <button type="button" onClick={handleLogout} disabled={isSubmitting} className={className}>
      {isSubmitting ? "ログアウト中..." : "ログアウト"}
    </button>
  );
}
export default LogoutButton;
