import { Link } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";

const features = [
  { title: "ニュース自動収集", description: "RSS / Atomから複数の配信元の記事をまとめて確認。" },
  { title: "タイトル・概要の日本語翻訳", description: "DeepLで新規記事のタイトルと概要を日本語に翻訳。" },
  { title: "キーワード検索", description: "原文・翻訳タイトルや概要から、気になる記事を検索。" },
  { title: "お気に入り・あとで読む", description: "残したい記事や時間のあるときに読みたい記事を保存。" },
  { title: "既読管理・メモ", description: "読んだ記事を整理し、気づきや学びをメモ。" },
  { title: "配信元の購読管理", description: "興味のある配信元を購読し、自分に合った情報収集を。" },
];

const buttonClass =
  "inline-flex items-center justify-center rounded-md px-6 py-3 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-2";

function HomePage() {
  const { user, isCheckingAuth } = useAuth();

  return (
    <main className="flex-1">
      <section className="border-b border-emerald-100 bg-emerald-50/60 px-4 py-14 sm:px-6 sm:py-20" aria-labelledby="home-heading">
        <div className="mx-auto max-w-5xl text-center">
          <p className="mb-4 text-lg font-bold tracking-wide text-emerald-800">テクっと</p>
          <h1 id="home-heading" className="text-3xl font-bold leading-relaxed tracking-tight text-balance text-stone-900 sm:text-5xl sm:leading-snug">
            気になるテックニュースを、<wbr />まとめてチェック。
          </h1>
          <p className="mx-auto mt-6 max-w-xl text-base leading-8 text-stone-600">
            RSS / Atomで配信される複数のテックニュースをまとめて確認。
            気になる配信元を購読し、記事を検索・保存して、日々の情報収集に役立てられます。
          </p>
          <p className="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">
            HTTPSのRSS / Atom URLであれば、テック系以外の配信元も追加できます。
          </p>
          <div className="mt-8">
            {isCheckingAuth ? (
              <p className="py-3 text-sm text-stone-500" role="status">ログイン状態を確認中...</p>
            ) : user ? (
              <Link to="/articles" className={`${buttonClass} bg-emerald-700 text-white hover:bg-emerald-800`}>
                記事一覧へ
              </Link>
            ) : (
              <>
                <div className="flex flex-col justify-center gap-3 sm:flex-row">
                  <Link to="/login" className={`${buttonClass} border border-emerald-700 bg-white text-emerald-800 hover:bg-emerald-50`}>
                    ログイン
                  </Link>
                  <Link to="/register" className={`${buttonClass} bg-emerald-700 text-white hover:bg-emerald-800`}>
                    新規登録
                  </Link>
                </div>
                <p className="mt-4 text-xs leading-relaxed text-stone-500">新規登録にはメール認証が必要です。</p>
              </>
            )}
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16" aria-labelledby="features-heading">
        <h2 id="features-heading" className="mb-8 text-center text-2xl font-semibold text-stone-900">主な機能</h2>
        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {features.map(({ title, description }) => (
            <li key={title} className="rounded-lg border border-stone-200 bg-white p-6">
              <h3 className="mb-3 font-semibold text-emerald-800">{title}</h3>
              <p className="text-sm leading-7 text-stone-600">{description}</p>
            </li>
          ))}
        </ul>
      </section>
    </main>
  );
}

export default HomePage;
