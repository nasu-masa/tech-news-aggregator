import { Link } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";
import LogoutButton from "../LogoutButton";

function Header() {
  const { user } = useAuth();

  return (
    <header className="border-b border-gray-200 bg-white">
      <div className="mx-auto flex h-16 max-w-screen-xl items-center justify-between gap-4 px-4 sm:px-6">
        <Link
          to="/"
          className="text-base font-semibold tracking-tight text-green-800 transition-colors hover:text-green-700 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 sm:text-lg"
        >
          Tech News Aggregator
        </Link>
        <nav aria-label="メインナビゲーション" className="flex items-center gap-2 sm:gap-3">
          {user ? (
            <LogoutButton className="cursor-pointer rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40" />
          ) : (
            <>
              <Link
                to="/login"
                className="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
              >
                ログイン
              </Link>
              <Link
                to="/register"
                className="rounded-md bg-green-700 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
              >
                会員登録
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}

export default Header;
