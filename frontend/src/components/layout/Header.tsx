import { Link } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";
import LogoutButton from "../LogoutButton";

function Header() {
  const { user } = useAuth();

  return (
    <header className="bg-white border-b border-green-100">
      <div className="max-w-screen-xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
        <Link
          to="/"
          className="text-green-800 font-semibold text-lg tracking-tight hover:text-green-700"
        >
          Tech News Aggregator
        </Link>
        <nav className="flex items-center gap-3">
          {user ? (
            <LogoutButton className="text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded px-3 py-1.5 cursor-pointer" />
          ) : (
            <>
              <Link
                to="/login"
                className="text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded px-3 py-1.5"
              >
                ログイン
              </Link>
              <Link
                to="/register"
                className="text-sm bg-green-700 text-white rounded px-3 py-1.5 hover:bg-green-800"
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
