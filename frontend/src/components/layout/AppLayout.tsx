import { Link, Outlet } from "react-router-dom";
import Header from "./Header";

function AppLayout() {
  return (
    <div className="flex min-h-svh flex-1 flex-col bg-stone-50">
      <Header />
      <Outlet />
      <footer className="border-t border-stone-200 bg-white py-4">
        <div className="mx-auto max-w-screen-2xl px-4 sm:px-6">
          <div className="flex flex-wrap gap-x-4 gap-y-2">
            <Link
              to="/terms"
              className="text-sm text-stone-500 hover:text-stone-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:rounded"
            >
              利用規約
            </Link>
            <Link
              to="/privacy"
              className="text-sm text-stone-500 hover:text-stone-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:rounded"
            >
              プライバシーポリシー
            </Link>
            <Link
              to="/contact"
              className="text-sm text-stone-500 hover:text-stone-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:rounded"
            >
              お問い合わせ
            </Link>
          </div>
        </div>
      </footer>
    </div>
  );
}

export default AppLayout;
