import { useState, type SyntheticEvent } from "react";
import { useNavigate } from "react-router-dom";
import { login } from "../lib/auth";

function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const navigate = useNavigate();

  const handleSubmit = async (e: SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (isSubmitting) return;

    setIsSubmitting(true);
    setErrorMessage("");

    try {
      await login({
        email,
        password,
      });

      navigate("/", { replace: true });
    } catch {
      setErrorMessage("ログインに失敗しました。");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="メールアドレス"
      />
      <input
        type="password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        placeholder="パスワード"
      />

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "ログイン中..." : "ログイン"}
      </button>

      {errorMessage && <p>{errorMessage}</p>}
    </form>
  );
}

export default LoginPage;
