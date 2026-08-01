import axios from "axios";
import {
  useCallback,
  useEffect,
  useRef,
  useState,
  type ReactNode,
} from "react";
import { getCurrentUser, type User } from "../lib/auth";
import { AuthContext } from "./auth-context";

type AuthProviderProps = {
  children: ReactNode;
};

export function AuthProvider({ children }: AuthProviderProps) {
  const [user, setUser] = useState<User | null>(null);
  const [isCheckingAuth, setIsCheckingAuth] = useState(true);
  const [authError, setAuthError] = useState<unknown | null>(null);
  const initialCheckStarted = useRef(false);

  const refreshUser = useCallback(async (): Promise<User | null> => {
    setIsCheckingAuth(true);
    setAuthError(null);

    try {
      const currentUser = await getCurrentUser();
      setUser(currentUser);

      return currentUser;
    } catch (error: unknown) {
      if (
        axios.isAxiosError(error) &&
        [401, 419].includes(error.response?.status ?? 0)
      ) {
        setUser(null);

        return null;
      }

      setAuthError(error);
      throw error;
    } finally {
      setIsCheckingAuth(false);
    }
  }, []);

  const clearUser = useCallback(() => {
    setUser(null);
    setAuthError(null);
    setIsCheckingAuth(false);
  }, []);

  useEffect(() => {
    if (initialCheckStarted.current) {
      return;
    }

    initialCheckStarted.current = true;
    void refreshUser().catch(() => undefined);
  }, [refreshUser]);

  return (
    <AuthContext.Provider
      value={{
        user,
        isCheckingAuth,
        authError,
        refreshUser,
        clearUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}
