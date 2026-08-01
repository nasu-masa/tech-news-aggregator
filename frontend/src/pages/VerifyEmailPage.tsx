import { useState } from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { resendVerificationEmail } from "../lib/auth";

function VerifyEmailPage() {
  const { user } = useAuth();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState("");

  const handleResend = async () => {
    if (isSubmitting) return;

    setIsSubmitting(true);
    setMessage("");

    try {
      await resendVerificationEmail();
      setMessage("認証メールを再送しました");
    } catch {
      setMessage("認証メールの再送に失敗しました");
    } finally {
      setIsSubmitting(false);
    }
  };

  if (user === null) {
    return null;
  }

  if (user.email_verified_at !== null) {
    return <Navigate to="/" replace />;
  }

  return (
    <main>
      <h1>登録していただいたメールアドレスに認証メールを送付しました。</h1>
      <p>メール認証を完了してください。</p>

      {import.meta.env.DEV && (
        <a href="http://localhost:8025" target="_blank" rel="noreferrer">
          開発用メールボックスを開く
        </a>
      )}

      <button type="button" onClick={handleResend} disabled={isSubmitting}>
        {isSubmitting ? "再送信中..." : "認証メールを再送する"}
      </button>

      {message && <p>{message}</p>}
    </main>
  );
}
export default VerifyEmailPage;
