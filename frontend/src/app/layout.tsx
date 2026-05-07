import "./globals.css";
import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Cafe PKL",
  description: "Frontend Next.js untuk aplikasi cafe PKL",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="id">
      <body>
        <div className="shell">
          <header className="topbar">
            <Link href="/" className="brand">Cafe PKL</Link>
            <nav className="nav">
              <Link href="/">Pesan</Link>
              <Link href="/login">Login Staf</Link>
              <Link href="/dashboard">Dashboard</Link>
            </nav>
          </header>
          <main className="wrap">{children}</main>
        </div>
      </body>
    </html>
  );
}
