import type { Role, User } from "./types";

export function dashboardPath(user: User) {
  if (user.role === "super_admin") return "/admin";
  if (user.role === "kasir") return "/kasir";
  if (user.role === "kitchen") return "/kitchen";
  return "/barista";
}

export function canAccess(user: User | null, roles: Role[]) {
  return !!user && roles.includes(user.role);
}
