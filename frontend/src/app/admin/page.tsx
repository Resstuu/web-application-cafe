"use client";

import { useEffect, useState } from "react";
import { apiFetch, getMenus, rupiah } from "@/lib/api";
import type { Menu, User } from "@/lib/types";

export default function AdminPage() {
  const [menus, setMenus] = useState<Menu[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [message, setMessage] = useState("");

  async function load() {
    const [menuRes, userRes] = await Promise.all([
      apiFetch<{ data: Menu[] }>("/admin/menus"),
      apiFetch<{ data: User[] }>("/admin/users"),
    ]);
    setMenus(menuRes.data);
    setUsers(userRes.data);
  }

  useEffect(() => { load().catch((err) => setMessage(err.message)); }, []);

  async function createMenu(form: FormData) {
    await apiFetch("/admin/menus", {
      method: "POST",
      body: JSON.stringify({
        name: form.get("name"),
        category: form.get("category"),
        price: Number(form.get("price")),
        description: form.get("description"),
        is_active: form.get("is_active") === "on",
      }),
    });
    await load();
  }

  async function createUser(form: FormData) {
    await apiFetch("/admin/users", {
      method: "POST",
      body: JSON.stringify({
        name: form.get("name"),
        email: form.get("email"),
        password: form.get("password"),
        role: form.get("role"),
        is_active: form.get("is_active") === "on",
      }),
    });
    await load();
  }

  return (
    <div className="grid two">
      <section className="panel">
        <h1>Super Admin</h1>
        {message && <div className="error">{message}</div>}
        <h2>Tambah Menu</h2>
        <form action={async (form) => { await createMenu(form); }}>
          <label>Nama</label><input name="name" required />
          <label>Kategori</label><select name="category"><option value="makanan">Makanan</option><option value="minuman">Minuman</option></select>
          <label>Harga</label><input name="price" type="number" required />
          <label>Deskripsi</label><textarea name="description" rows={2} />
          <label className="row" style={{ fontWeight: 400 }}><input name="is_active" type="checkbox" defaultChecked style={{ width: "auto" }} /> Aktif</label>
          <button className="button">Simpan Menu</button>
        </form>
        <h2 style={{ marginTop: 28 }}>Tambah User</h2>
        <form action={async (form) => { await createUser(form); }}>
          <label>Nama</label><input name="name" required />
          <label>Email</label><input name="email" type="email" required />
          <label>Password</label><input name="password" type="password" required />
          <label>Role</label><select name="role"><option value="kasir">Kasir</option><option value="kitchen">Kitchen</option><option value="barista">Barista</option><option value="super_admin">Super Admin</option></select>
          <label className="row" style={{ fontWeight: 400 }}><input name="is_active" type="checkbox" defaultChecked style={{ width: "auto" }} /> Aktif</label>
          <button className="button">Simpan User</button>
        </form>
      </section>
      <section className="grid">
        <div className="panel">
          <h2>Menu</h2>
          <table><tbody>{menus.map((menu) => (
            <tr key={menu.id}><td>{menu.name}<br /><span className="badge">{menu.category}</span></td><td>{rupiah(menu.price)}</td><td>{menu.is_active ? "Aktif" : "Nonaktif"}</td></tr>
          ))}</tbody></table>
        </div>
        <div className="panel">
          <h2>User</h2>
          <table><tbody>{users.map((user) => (
            <tr key={user.id}><td>{user.name}<br /><span className="muted">{user.email}</span></td><td><span className="badge">{user.role}</span></td><td>{user.is_active ? "Aktif" : "Nonaktif"}</td></tr>
          ))}</tbody></table>
        </div>
      </section>
    </div>
  );
}
