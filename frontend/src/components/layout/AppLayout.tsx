import { Outlet } from "react-router-dom";
import Header from "./Header";

function AppLayout() {
  return (
    <div className="flex min-h-svh flex-1 flex-col bg-stone-50">
      <Header />
      <Outlet />
    </div>
  );
}

export default AppLayout;
