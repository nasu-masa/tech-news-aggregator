import { Outlet } from "react-router-dom";
import DesktopSidebar from "./DesktopSidebar";

function ArticleLayout() {
  return (
    <div className="mx-auto flex w-full max-w-screen-2xl flex-1 gap-6 lg:px-6">
      <DesktopSidebar />
      <div className="min-w-0 flex-1">
        <Outlet />
      </div>
    </div>
  );
}

export default ArticleLayout;
