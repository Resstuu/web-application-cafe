"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { dashboardPath } from "@/lib/auth";
import { me } from "@/lib/api";

export default function DashboardPage() {
  const router = useRouter();

  useEffect(() => {
    me()
      .then(({ user }) => router.replace(dashboardPath(user)))
      .catch(() => router.replace("/login"));
  }, [router]);

  return <section className="panel">Mengarahkan ke dashboard...</section>;
}
