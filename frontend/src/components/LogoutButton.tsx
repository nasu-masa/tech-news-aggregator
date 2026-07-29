import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { logout } from "../lib/auth";

function LogoutButton() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const navigate = useNavigate();

  const handleLogout = async () => {
    if (isSubmitting) return;

    setIsSubmitting(true);

    try {
      await logout();

      navigate("/login", { replace: true });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <button type="button" onClick={handleLogout} disabled={isSubmitting}>
      {isSubmitting ? "ログアウト中..." : "ログアウト"}
    </button>
  );
}
export default LogoutButton;
