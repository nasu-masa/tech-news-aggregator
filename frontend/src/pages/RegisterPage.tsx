import { useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { registerUser, type RegisterInput } from "../lib/auth";
import setValidationErrors from "../lib/formValidation";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";

const inputClass =
  "block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 placeholder:text-gray-400 transition-colors focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20 aria-[invalid=true]:border-red-400 aria-[invalid=true]:focus:ring-red-400/20";

function RegisterPage() {
  const [errorMessage, setErrorMessage] = useState("");

  const navigate = useNavigate();
  const { refreshUser } = useAuth();

  const {
    register,
    handleSubmit,
    watch,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<RegisterInput>();

  const password = watch("password");

  const onSubmit: SubmitHandler<RegisterInput> = async (data) => {
    setErrorMessage("");

    try {
      await registerUser(data);
      await refreshUser();

      navigate("/verify-email", { replace: true });
    } catch (error) {
      const handled = setValidationErrors(error, setError, [
        "name",
        "email",
        "password",
        "password_confirmation",
      ]);

      if (!handled) {
        setErrorMessage("会員登録に失敗しました。");
      }
    }
  };

  return (
    <div className="flex flex-1 items-center justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-sm rounded-lg border border-gray-200 bg-white p-10 shadow-sm">
        <h1 className="mb-7 text-xl font-semibold tracking-tight text-gray-900">
          会員登録
        </h1>
        <form onSubmit={handleSubmit(onSubmit)} noValidate>
          <div className="mb-5">
            <label
              className="mb-1.5 block text-sm font-medium text-gray-700"
              htmlFor="name"
            >
              名前
            </label>
            <input
              className={inputClass}
              type="text"
              id="name"
              aria-invalid={errors.name ? "true" : "false"}
              aria-describedby={errors.name ? "name-error" : undefined}
              {...register("name", { required: "名前を入力してください" })}
              placeholder="例：佐藤 太郎"
            />
            {errors.name && (
              <p
                className="mt-1.5 text-sm text-red-600"
                id="name-error"
                role="alert"
              >
                {errors.name.message}
              </p>
            )}
          </div>

          <div className="mb-5">
            <label
              className="mb-1.5 block text-sm font-medium text-gray-700"
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
              placeholder="例：example@example.com"
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

          <div className="mb-5">
            <label
              className="mb-1.5 block text-sm font-medium text-gray-700"
              htmlFor="password"
            >
              パスワード
            </label>
            <input
              className={inputClass}
              type="password"
              id="password"
              aria-invalid={errors.password ? "true" : "false"}
              aria-describedby={errors.password ? "password-error" : undefined}
              {...register("password", {
                required: "パスワードを入力してください",
                minLength: {
                  value: 8,
                  message: "パスワードは８文字以上で入力してください",
                },
              })}
            />
            {errors.password && (
              <p
                className="mt-1.5 text-sm text-red-600"
                id="password-error"
                role="alert"
              >
                {errors.password.message}
              </p>
            )}
          </div>

          <div className="mb-5">
            <label
              className="mb-1.5 block text-sm font-medium text-gray-700"
              htmlFor="password_confirmation"
            >
              パスワード確認
            </label>
            <input
              className={inputClass}
              type="password"
              id="password_confirmation"
              aria-invalid={errors.password_confirmation ? "true" : "false"}
              aria-describedby={
                errors.password_confirmation
                  ? "password-confirmation-error"
                  : undefined
              }
              {...register("password_confirmation", {
                required: "確認用パスワードを入力してください",
                deps: ["password"],
                validate: (value) =>
                  value === password || "パスワードが一致しません",
              })}
            />
            {errors.password_confirmation && (
              <p
                className="mt-1.5 text-sm text-red-600"
                id="password-confirmation-error"
                role="alert"
              >
                {errors.password_confirmation.message}
              </p>
            )}
          </div>

          <button
            className="mt-2 block w-full rounded-md bg-green-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
            type="submit"
            disabled={isSubmitting}
          >
            {isSubmitting ? "送信中..." : "会員登録"}
          </button>

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

export default RegisterPage;
