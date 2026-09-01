import { useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { Link, useNavigate } from "react-router-dom";
import { deleteAccount } from "../lib/auth";
import setValidationErrors from "../lib/formValidation";
import { useAuth } from "../hooks/useAuth";

const inputClass =
  "block w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-base text-stone-900 placeholder:text-stone-400 transition-colors focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/20 aria-[invalid=true]:border-red-400 aria-[invalid=true]:focus:ring-red-400/20";

type DeleteFormInput = {
  password: string;
};

function DeleteAccountPage() {
  const { clearUser } = useAuth();
  const navigate = useNavigate();
  const [deleteError, setDeleteError] = useState("");

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<DeleteFormInput>();

  const onSubmit: SubmitHandler<DeleteFormInput> = async (data) => {
    setDeleteError("");
    try {
      await deleteAccount(data.password);
      clearUser();
      navigate("/login", {
        state: { message: "アカウントを削除しました。ご利用ありがとうございました。" },
      });
    } catch (error) {
      const handled = setValidationErrors(error, setError, ["password"]);
      if (!handled) setDeleteError("アカウントの削除に失敗しました。");
    }
  };

  return (
    <div className="flex flex-1 justify-center bg-stone-50 px-4 py-12 text-left">
      <div className="w-full max-w-xl space-y-6">
        <h1 className="text-2xl font-semibold tracking-tight text-stone-900">
          アカウントを削除
        </h1>

        <section className="rounded-lg border border-red-200 bg-white p-8 shadow-sm">
          <h2 className="mb-4 text-base font-semibold text-red-700">
            この操作は取り消せません
          </h2>

          <p className="mb-4 text-sm text-stone-700">
            アカウントを削除すると、以下のデータがすべて完全に削除されます。
          </p>

          <ul className="mb-6 list-inside list-disc space-y-1.5 text-sm text-stone-600">
            <li>アカウント情報（名前・メールアドレス）</li>
            <li>購読しているフィード設定</li>
            <li>記事の既読状態・メモ</li>
          </ul>

          <form onSubmit={handleSubmit(onSubmit)} noValidate>
            <div className="mb-5">
              <label
                className="mb-1.5 block text-sm font-medium text-stone-700"
                htmlFor="delete-password"
              >
                確認のため、現在のパスワードを入力してください
              </label>
              <input
                className={inputClass}
                type="password"
                id="delete-password"
                autoComplete="current-password"
                aria-invalid={errors.password ? "true" : "false"}
                aria-describedby={errors.password ? "delete-password-error" : undefined}
                {...register("password", {
                  required: "パスワードを入力してください",
                })}
              />
              {errors.password && (
                <p className="mt-1.5 text-sm text-red-600" id="delete-password-error" role="alert">
                  {errors.password.message}
                </p>
              )}
            </div>

            <div className="flex gap-3">
              <button
                type="submit"
                disabled={isSubmitting}
                className="rounded-md bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {isSubmitting ? "削除中..." : "アカウントを削除"}
              </button>
              <Link
                to="/settings"
                className="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
              >
                キャンセル
              </Link>
            </div>

            {deleteError && (
              <p className="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700" role="alert">
                {deleteError}
              </p>
            )}
          </form>
        </section>
      </div>
    </div>
  );
}

export default DeleteAccountPage;
