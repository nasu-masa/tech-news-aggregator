import { useState } from "react";
import { Navigate, useLocation, useSearchParams } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { resendVerificationEmail } from "../lib/auth";

function VerifyEmailPage() {
  const { user } = useAuth();
  const location = useLocation();
  const [searchParams] = useSearchParams();
  const successMessage = (location.state as { message?: string } | null)?.message;
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
    return (
      <Navigate
        to="/articles"
        replace
        state={searchParams.get("verified") === "1" ? { emailVerified: true } : null}
      />
    );
  }

  return (
    <main className="flex flex-1 items-center justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-sm rounded-lg border border-stone-200 bg-white p-10 shadow-sm">
        <h1 className="mb-3 text-xl font-semibold tracking-tight text-stone-900">
          メールを確認してください
        </h1>
        <p className="mb-7 text-sm leading-relaxed text-stone-600">
          登録いただいたメールアドレスに認証メールを送信しました。
        </p>

        {successMessage && (
          <p
            className="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-700"
            role="status"
          >
            {successMessage}
          </p>
        )}

        <button
          className="block w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
          type="button"
          onClick={handleResend}
          disabled={isSubmitting}
        >
          {isSubmitting ? "再送信中..." : "認証メールを再送する"}
        </button>

        {message && (
          <p
            className={`mt-3 text-sm ${isResendError ? "text-red-600" : "text-emerald-700"}`}
            role={isResendError ? "alert" : undefined}
          >
            {message}
          </p>
        )}

        {import.meta.env.DEV && (
          <a
            className="mt-5 inline-block text-xs text-stone-400 underline underline-offset-2 hover:text-stone-600"
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
