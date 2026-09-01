import { useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth";
import LogoutButton from "../LogoutButton";
import SourceManageModal from "../sources/SourceManageModal";

function Header() {
  const { user } = useAuth();
  const [isSourceModalOpen, setIsSourceModalOpen] = useState(false);

  return (
    <>
      <header className="border-b border-stone-200 bg-white">
        <div className="mx-auto flex h-16 max-w-screen-2xl items-center justify-between gap-4 px-4 sm:px-6">
          <Link
            to="/"
            className="text-xl font-bold tracking-normal text-emerald-800 transition-colors hover:text-emerald-700 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 sm:text-2xl"
          >
            テクっと
          </Link>
          <nav aria-label="メインナビゲーション" className="flex items-center gap-2 sm:gap-3">
            {user ? (
              <>
                <button
                  type="button"
                  onClick={() => setIsSourceModalOpen(true)}
                  className="rounded-md border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
                >
                  配信元を管理
                </button>
                <Link
                  to="/settings"
                  className="rounded-md border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
                >
                  設定
                </Link>
                <LogoutButton className="cursor-pointer rounded-md border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40" />
              </>
            ) : (
              <>
                <Link
                  to="/login"
                  className="rounded-md border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:bg-stone-50 hover:text-stone-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
                >
                  ログイン
                </Link>
                <Link
                  to="/register"
                  className="rounded-md bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
                >
                  会員登録
                </Link>
              </>
            )}
          </nav>
        </div>
      </header>
      <SourceManageModal
        isOpen={isSourceModalOpen}
        onClose={() => setIsSourceModalOpen(false)}
      />
    </>
  );
}

export default Header;
