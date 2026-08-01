import { useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { registerUser, type RegisterInput } from "../lib/auth";
import setValidationErrors from "../lib/formValidation";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";

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

  // 確認用パスワードと比較するため、現在のパスワード入力値を取得
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
    <>
      <form onSubmit={handleSubmit(onSubmit)} noValidate>
        <label htmlFor="name">名前</label>
        <input
          type="text"
          id="name"
          aria-invalid={errors.name ? "true" : "false"}
          aria-describedby={errors.name ? "name-error" : undefined}
          {...register("name", { required: "名前を入力してください" })}
          placeholder="例：佐藤 太郎"
        />
        {errors.name && (
          <p id="name-error" role="alert">
            {errors.name.message}
          </p>
        )}

        <label htmlFor="email">メールアドレス</label>
        <input
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
          <p id="email-error" role="alert">
            {errors.email.message}
          </p>
        )}

        <label htmlFor="password">パスワード</label>
        <input
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
          <p id="password-error" role="alert">
            {errors.password.message}
          </p>
        )}

        <label htmlFor="password_confirmation">パスワード確認</label>
        <input
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
          <p id="password-confirmation-error" role="alert">
            {errors.password_confirmation.message}
          </p>
        )}

        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? "送信中..." : "会員登録"}
        </button>

        {errorMessage && <p>{errorMessage}</p>}
      </form>
    </>
  );
}
export default RegisterPage;
