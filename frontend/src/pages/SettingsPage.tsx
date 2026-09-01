import { useEffect, useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { Link, useNavigate } from "react-router-dom";
import {
  updatePassword,
  updateProfile,
  type UpdatePasswordInput,
  type UpdateProfileInput,
} from "../lib/auth";
import setValidationErrors from "../lib/formValidation";
import { useAuth } from "../hooks/useAuth";

const inputClass =
  "block w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-base text-stone-900 placeholder:text-stone-400 transition-colors focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/20 aria-[invalid=true]:border-red-400 aria-[invalid=true]:focus:ring-red-400/20";

function SettingsPage() {
  const { user, refreshUser } = useAuth();
  const navigate = useNavigate();

  // --- Profile section ---
  const [profileSuccess, setProfileSuccess] = useState("");
  const [profileError, setProfileError] = useState("");
  const {
    register: registerProfile,
    handleSubmit: handleProfileSubmit,
    setError: setProfileError_,
    reset: resetProfile,
    formState: { errors: profileErrors, isSubmitting: isProfileSubmitting },
  } = useForm<UpdateProfileInput>();

  useEffect(() => {
    if (user) {
      resetProfile({ name: user.name, email: user.email });
    }
  }, [user, resetProfile]);

  const onProfileSubmit: SubmitHandler<UpdateProfileInput> = async (data) => {
    setProfileSuccess("");
    setProfileError("");

    try {
      await updateProfile(data);
      const updated = await refreshUser();

      if (updated && !updated.email_verified_at) {
        navigate("/verify-email", {
          state: { message: "メールアドレスが変更されました。新しいアドレスへの確認メールをご確認ください。" },
        });
        return;
      }

      setProfileSuccess("プロフィールを更新しました。");
    } catch (error) {
      const handled = setValidationErrors(error, setProfileError_, ["name", "email"]);
      if (!handled) setProfileError("プロフィールの更新に失敗しました。");
    }
  };

  // --- Password section ---
  const [passwordSuccess, setPasswordSuccess] = useState("");
  const [passwordError, setPasswordError] = useState("");
  const {
    register: registerPassword,
    handleSubmit: handlePasswordSubmit,
    setError: setPasswordError_,
    reset: resetPassword_,
    watch: watchPassword,
    formState: { errors: passwordErrors, isSubmitting: isPasswordSubmitting },
  } = useForm<UpdatePasswordInput>();

  const newPassword = watchPassword("password");

  const onPasswordSubmit: SubmitHandler<UpdatePasswordInput> = async (data) => {
    setPasswordSuccess("");
    setPasswordError("");

    try {
      await updatePassword(data);
      setPasswordSuccess("パスワードを変更しました。");
      resetPassword_();
    } catch (error) {
      const handled = setValidationErrors(error, setPasswordError_, [
        "current_password",
        "password",
        "password_confirmation",
      ]);
      if (!handled) setPasswordError("パスワードの変更に失敗しました。");
    }
  };

  return (
    <div className="flex flex-1 justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-xl space-y-6">
        <h1 className="text-2xl font-semibold tracking-tight text-stone-900">
          アカウント設定
        </h1>

        {/* Profile section */}
        <section className="rounded-lg border border-stone-200 bg-white p-8 shadow-sm">
          <h2 className="mb-6 text-base font-semibold text-stone-900">
            プロフィール情報
          </h2>
          <form onSubmit={handleProfileSubmit(onProfileSubmit)} noValidate>
            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="profile-name"
              >
                名前
              </label>
              <input
                className={inputClass}
                type="text"
                id="profile-name"
                aria-invalid={profileErrors.name ? "true" : "false"}
                aria-describedby={profileErrors.name ? "profile-name-error" : undefined}
                {...registerProfile("name", { required: "名前を入力してください" })}
              />
              {profileErrors.name && (
                <p className="mt-1.5 text-sm text-red-600" id="profile-name-error" role="alert">
                  {profileErrors.name.message}
                </p>
              )}
            </div>

            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="profile-email"
              >
                メールアドレス
              </label>
              <input
                className={inputClass}
                type="email"
                id="profile-email"
                aria-invalid={profileErrors.email ? "true" : "false"}
                aria-describedby={profileErrors.email ? "profile-email-error" : undefined}
                {...registerProfile("email", {
                  required: "メールアドレスを入力してください",
                  pattern: {
                    value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: "メールアドレス形式で入力してください",
                  },
                })}
              />
              {profileErrors.email && (
                <p className="mt-1.5 text-sm text-red-600" id="profile-email-error" role="alert">
                  {profileErrors.email.message}
                </p>
              )}
              <p className="mt-1.5 text-xs text-stone-500">
                メールアドレスを変更すると再認証が必要になります。
              </p>
            </div>

            <button
              className="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
              type="submit"
              disabled={isProfileSubmitting}
            >
              {isProfileSubmitting ? "保存中..." : "保存"}
            </button>

            {profileSuccess && (
              <p className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-700" role="status">
                {profileSuccess}
              </p>
            )}
            {profileError && (
              <p className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700" role="alert">
                {profileError}
              </p>
            )}
          </form>
        </section>

        {/* Password section */}
        <section className="rounded-lg border border-stone-200 bg-white p-8 shadow-sm">
          <h2 className="mb-6 text-base font-semibold text-stone-900">
            パスワード変更
          </h2>
          <form onSubmit={handlePasswordSubmit(onPasswordSubmit)} noValidate>
            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="current-password"
              >
                現在のパスワード
              </label>
              <input
                className={inputClass}
                type="password"
                id="current-password"
                autoComplete="current-password"
                aria-invalid={passwordErrors.current_password ? "true" : "false"}
                aria-describedby={passwordErrors.current_password ? "current-password-error" : undefined}
                {...registerPassword("current_password", {
                  required: "現在のパスワードを入力してください",
                })}
              />
              {passwordErrors.current_password && (
                <p className="mt-1.5 text-sm text-red-600" id="current-password-error" role="alert">
                  {passwordErrors.current_password.message}
                </p>
              )}
            </div>

            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="new-password"
              >
                新しいパスワード
              </label>
              <input
                className={inputClass}
                type="password"
                id="new-password"
                autoComplete="new-password"
                aria-invalid={passwordErrors.password ? "true" : "false"}
                aria-describedby={passwordErrors.password ? "new-password-error" : undefined}
                {...registerPassword("password", {
                  required: "新しいパスワードを入力してください",
                  minLength: {
                    value: 8,
                    message: "パスワードは8文字以上で入力してください",
                  },
                })}
              />
              {passwordErrors.password && (
                <p className="mt-1.5 text-sm text-red-600" id="new-password-error" role="alert">
                  {passwordErrors.password.message}
                </p>
              )}
            </div>

            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="password-confirmation"
              >
                新しいパスワード（確認）
              </label>
              <input
                className={inputClass}
                type="password"
                id="password-confirmation"
                autoComplete="new-password"
                aria-invalid={passwordErrors.password_confirmation ? "true" : "false"}
                aria-describedby={passwordErrors.password_confirmation ? "password-confirmation-error" : undefined}
                {...registerPassword("password_confirmation", {
                  required: "確認用パスワードを入力してください",
                  deps: ["password"],
                  validate: (value) =>
                    value === newPassword || "パスワードが一致しません",
                })}
              />
              {passwordErrors.password_confirmation && (
                <p className="mt-1.5 text-sm text-red-600" id="password-confirmation-error" role="alert">
                  {passwordErrors.password_confirmation.message}
                </p>
              )}
            </div>

            <button
              className="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
              type="submit"
              disabled={isPasswordSubmitting}
            >
              {isPasswordSubmitting ? "変更中..." : "パスワードを変更"}
            </button>

            {passwordSuccess && (
              <p className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-700" role="status">
                {passwordSuccess}
              </p>
            )}
            {passwordError && (
              <p className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700" role="alert">
                {passwordError}
              </p>
            )}
          </form>
        </section>

        {/* Delete account section */}
        <section className="rounded-lg border border-red-200 bg-white p-8 shadow-sm">
          <h2 className="mb-2 text-base font-semibold text-red-700">
            アカウント削除
          </h2>
          <p className="mb-5 text-sm text-stone-600">
            アカウントとすべての関連データを完全に削除します。この操作は取り消せません。
          </p>
          <Link
            to="/settings/delete-account"
            className="inline-block rounded-md border border-red-300 px-4 py-2.5 text-sm font-medium text-red-700 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40"
          >
            退会する
          </Link>
        </section>
      </div>
    </div>
  );
}

export default SettingsPage;
