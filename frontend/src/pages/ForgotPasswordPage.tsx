import { useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { forgotPassword, type ForgotPasswordInput } from "../lib/auth";
import setValidationErrors from "../lib/formValidation";

const inputClass =
  "block w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-base text-stone-900 placeholder:text-stone-400 transition-colors focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/20 aria-[invalid=true]:border-red-400 aria-[invalid=true]:focus:ring-red-400/20";

function ForgotPasswordPage() {
  const [successMessage, setSuccessMessage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ForgotPasswordInput>();

  const onSubmit: SubmitHandler<ForgotPasswordInput> = async (data) => {
    setSuccessMessage("");
    setErrorMessage("");

    try {
      await forgotPassword(data);
      setSuccessMessage(
        "パスワードリセット用のメールを送信しました。\nメールをご確認ください。",
      );
    } catch (error) {
      const handled = setValidationErrors(error, setError, ["email"]);

      if (!handled) {
        setErrorMessage("送信に失敗しました。しばらくしてから再度お試しください。");
      }
    }
  };

  return (
    <div className="flex flex-1 items-center justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-sm rounded-lg border border-stone-200 bg-white p-10 shadow-sm">
        <h1 className="mb-7 text-xl font-semibold tracking-tight text-stone-900">
          パスワードをお忘れの方
        </h1>
        <form onSubmit={handleSubmit(onSubmit)} noValidate>
          <div className="mb-5">
            <label
              className="mb-1.5 block text-sm font-medium text-stone-700"
              htmlFor="email"
            >
              メールアドレス
            </label>
            <input
              className={inputClass}
              type="email"
              id="email"
              aria-invalid={errors.email ? "true" : "false"}
              aria-describedby={errors.email ? "email-error" : undefined}
              {...register("email", {
                required: "メールアドレスを入力してください",
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: "メールアドレス形式で入力してください",
                },
              })}
            />
            {errors.email && (
              <p
                className="mt-1.5 text-sm text-red-600"
                id="email-error"
                role="alert"
              >
                {errors.email.message}
              </p>
            )}
          </div>

          <button
            className="mt-2 block w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
            type="submit"
            disabled={isSubmitting}
          >
            {isSubmitting ? "送信中..." : "リセットメールを送信"}
          </button>

          {successMessage && (
            <p
              className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-700"
              role="status"
            >
              {successMessage}
            </p>
          )}

          {errorMessage && (
            <p
              className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700"
              role="alert"
            >
              {errorMessage}
            </p>
          )}
        </form>
      </div>
    </div>
  );
}

export default ForgotPasswordPage;
