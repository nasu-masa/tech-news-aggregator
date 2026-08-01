import { useState } from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { resendVerificationEmail } from "../lib/auth";

function VerifyEmailPage() {
  const { user } = useAuth();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState("");
  const [isResendError, setIsResendError] = useState(false);

  const handleResend = async () => {
    if (isSubmitting) return;

    setIsSubmitting(true);
    setMessage("");

    try {
      await resendVerificationEmail();
      setMessage("認証メールを再送しました");
      setIsResendError(false);
    } catch {
      setMessage("認証メールの再送に失敗しました");
      setIsResendError(true);
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
    <main className="flex flex-1 items-center justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-sm rounded-lg border border-gray-200 bg-white p-10 shadow-sm">
        <h1 className="mb-3 text-xl font-semibold tracking-tight text-gray-900">
          メールを確認してください
        </h1>
        <p className="mb-7 text-sm leading-relaxed text-gray-600">
          登録いただいたメールアドレスに認証メールを送信しました。
        </p>

        <button
          className="block w-full rounded-md bg-green-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
          type="button"
          onClick={handleResend}
          disabled={isSubmitting}
        >
          {isSubmitting ? "再送信中..." : "認証メールを再送する"}
        </button>

        {message && (
          <p
            className={`mt-3 text-sm ${isResendError ? "text-red-600" : "text-green-700"}`}
            role={isResendError ? "alert" : undefined}
          >
            {message}
          </p>
        )}

        {import.meta.env.DEV && (
          <a
            className="mt-5 inline-block text-xs text-gray-400 underline underline-offset-2 hover:text-gray-600"
            href="http://localhost:8025"
            target="_blank"
            rel="noreferrer"
          >
            開発用メールボックスを開く (MailHog)
          </a>
        )}
      </div>
    </main>
  );
}

export default VerifyEmailPage;
