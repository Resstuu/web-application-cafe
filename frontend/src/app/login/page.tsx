"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { dashboardPath } from "@/lib/auth";
import { login } from "@/lib/api";

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("admin@cafe.test");
  const [password, setPassword] = useState("password");
  const [error, setError] = useState("");

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    try {
      const user = await login(email, password);
      router.push(dashboardPath(user));
    } catch (err) {
      setError(err instanceof Error ? err.message : "Login gagal.");
    }
  }

  return (
    <section className="panel" style={{ maxWidth: 460, margin: "60px auto" }}>
      <h1>Login Staf</h1>
      <p className="muted">Masuk dengan akun Super Admin, Kasir, Kitchen, atau Barista.</p>
      {error && <div className="error">{error}</div>}
      <form onSubmit={submit}>
        <label>Email</label>
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
        <label>Password</label>
        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
        <button className="button full" style={{ marginTop: 14 }}>Login</button>
      </form>
    </section>
  );
}
