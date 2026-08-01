import { createContext } from "react";
import type { User } from "../lib/auth";

export type AuthContextValue = {
  user: User | null;
  isCheckingAuth: boolean;
  authError: unknown | null;
  refreshUser: () => Promise<User | null>;
  clearUser: () => void;
};

export const AuthContext = createContext<AuthContextValue | undefined>(
  undefined,
);
