import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useForm, type SubmitHandler } from "react-hook-form";
import { loginUser, type LoginInput } from "../lib/auth";
import setValidationErrors from "../lib/formValidation";

function LoginPage() {
  const [errorMessage, setErrorMessage] = useState("");

  const navigate = useNavigate();

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginInput>();

  const onSubmit: SubmitHandler<LoginInput> = async (data) => {
    setErrorMessage("");

    try {
      await loginUser(data);

      navigate("/", { replace: true });
    } catch (error) {
      const handled = setValidationErrors(error, setError, [
        "email",
        "password",
      ]);

      if (!handled) {
        setErrorMessage("ログインに失敗しました。");
      }
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} noValidate>
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
        })}
      />
      {errors.password && (
        <p id="password-error" role="alert">
          {errors.password.message}
        </p>
      )}

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "ログイン中..." : "ログイン"}
      </button>

      {errorMessage && <p>{errorMessage}</p>}
    </form>
  );
}

export default LoginPage;
