import { Link, Outlet } from "react-router-dom";
import Header from "./Header";

function AppLayout() {
  return (
    <div className="flex min-h-svh flex-1 flex-col bg-stone-50">
      <Header />
      <Outlet />
      <footer className="border-t border-stone-200 bg-white py-4">
        <div className="mx-auto max-w-screen-2xl px-4 sm:px-6">
          <Link
            to="/contact"
            className="text-sm text-stone-500 hover:text-stone-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:rounded"
          >
            お問い合わせ
          </Link>
        </div>
      </footer>
    </div>
  );
}

export default AppLayout;
