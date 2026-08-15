import { NavLink } from "react-router-dom";

function DesktopSidebar() {
  return (
    <aside className="hidden w-56 shrink-0 py-10 lg:block">
      <nav
        aria-label="記事ナビゲーション"
        className="rounded-lg border border-gray-200 bg-white p-3"
      >
        <p className="mb-2 px-3 text-xs font-semibold tracking-wide text-gray-500">
          記事メニュー
        </p>
        <NavLink
          to="/"
          end
          className={({ isActive }) =>
            [
              "block rounded-md px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40",
              isActive
                ? "bg-green-50 text-green-800"
                : "text-gray-700 hover:bg-gray-50 hover:text-green-800",
            ].join(" ")
          }
        >
          すべての記事
        </NavLink>
      </nav>
    </aside>
  );
}

export default DesktopSidebar;
