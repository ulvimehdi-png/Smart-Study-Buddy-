import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "@/lib/auth"

export default function ProtectedRoute({ children }) {
  const { isAuthenticated, authChecked } = useAuth();
  const location = useLocation();

  if (!authChecked) {
    return null;
  }

  return isAuthenticated ? children : <Navigate to="/login" state={{ from: location }} replace />;
}