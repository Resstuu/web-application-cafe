"use client";

import { useEffect, useMemo, useState } from "react";
import { apiFetch, getMenus, rupiah } from "@/lib/api";
import type { Menu } from "@/lib/types";

declare global {
  interface Window {
    snap?: {
      pay: (token: string, callbacks: Record<string, () => void>) => void;
    };
  }
}

type Cart = Record<number, number>;

export default function CustomerOrderPage() {
  const [menus, setMenus] = useState<Menu[]>([]);
  const [search, setSearch] = useState("");
  const [category, setCategory] = useState("");
  const [cart, setCart] = useState<Cart>({});
  const [customerName, setCustomerName] = useState("");
  const [tableNumber, setTableNumber] = useState("");
  const [message, setMessage] = useState("");

  useEffect(() => {
    const params = new URLSearchParams();
    if (search) params.set("search", search);
    if (category) params.set("category", category);
    getMenus(`?${params.toString()}`).then((res) => setMenus(res.data)).catch((err) => setMessage(err.message));
  }, [search, category]);

  useEffect(() => {
    const clientKey = process.env.NEXT_PUBLIC_MIDTRANS_CLIENT_KEY;
    if (!clientKey || document.querySelector("#midtrans-snap")) return;
    const script = document.createElement("script");
    script.id = "midtrans-snap";
    script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
    script.setAttribute("data-client-key", clientKey);
    document.body.appendChild(script);
  }, []);

  const selected = useMemo(() => menus
    .map((menu) => ({ menu, qty: cart[menu.id] ?? 0 }))
    .filter((item) => item.qty > 0), [menus, cart]);
  const total = selected.reduce((sum, item) => sum + item.menu.price * item.qty, 0);

  async function checkout() {
    setMessage("");
    if (!customerName || !tableNumber || selected.length === 0) {
      setMessage("Isi nama, nomor meja, dan pilih minimal satu menu.");
      return;
    }

    const data = await apiFetch<{
      snap_token: string;
      finish_url: string;
      unfinish_url: string;
      error_url: string;
      demo_payment: boolean;
      order: { code: string };
    }>("/customer/checkout", {
      method: "POST",
      body: JSON.stringify({
        customer_name: customerName,
        table_number: tableNumber,
        items: selected.map((item) => ({ menu_id: item.menu.id, qty: item.qty })),
      }),
    });

    if (data.demo_payment || !window.snap) {
      setMessage(`Order ${data.order.code} dibuat. Isi key Midtrans agar Snap terbuka.`);
      return;
    }

    window.snap.pay(data.snap_token, {
      onSuccess: () => { window.location.href = data.finish_url; },
      onPending: () => { window.location.href = data.unfinish_url; },
      onError: () => { window.location.href = data.error_url; },
      onClose: () => setMessage("Pembayaran belum selesai."),
    });
  }

  return (
    <div className="grid two" style={{ gridTemplateColumns: "1.6fr .9fr" }}>
      <section>
        <div className="row" style={{ marginBottom: 14 }}>
          <h1 style={{ margin: 0 }}>Pesan Menu</h1>
          <span className="spacer" />
          <span className="badge">Next.js + Laravel API</span>
        </div>
        <div className="row" style={{ marginBottom: 16 }}>
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari makanan atau minuman" style={{ flex: 1, minWidth: 240 }} />
          <select value={category} onChange={(e) => setCategory(e.target.value)} style={{ maxWidth: 180 }}>
            <option value="">Semua kategori</option>
            <option value="makanan">Makanan</option>
            <option value="minuman">Minuman</option>
          </select>
        </div>
        <div className="grid three">
          {menus.map((menu) => (
            <article className="card" key={menu.id}>
              <div className="row">
                <h3 style={{ margin: 0 }}>{menu.name}</h3>
                <span className="spacer" />
                <span className="badge">{menu.category}</span>
              </div>
              <p className="muted">{menu.description ?? "Menu cafe siap dipesan."}</p>
              <div className="row">
                <span className="price">{rupiah(menu.price)}</span>
                <span className="spacer" />
                <input style={{ width: 84 }} type="number" min={0} value={cart[menu.id] ?? 0} onChange={(e) => setCart({ ...cart, [menu.id]: Number(e.target.value) })} />
              </div>
            </article>
          ))}
        </div>
      </section>

      <aside className="panel sticky">
        <h2>Checkout</h2>
        <label>Nama pelanggan</label>
        <input value={customerName} onChange={(e) => setCustomerName(e.target.value)} placeholder="Contoh: Restu" />
        <label>Nomor meja</label>
        <input value={tableNumber} onChange={(e) => setTableNumber(e.target.value)} placeholder="Contoh: 07" />
        <div style={{ margin: "16px 0" }}>
          {selected.length === 0 ? <p className="muted">Belum ada menu dipilih.</p> : selected.map((item) => (
            <div className="row" key={item.menu.id}>
              <span>{item.menu.name} x {item.qty}</span>
              <span className="spacer" />
              <strong>{rupiah(item.menu.price * item.qty)}</strong>
            </div>
          ))}
        </div>
        <div className="row"><strong>Total</strong><span className="spacer" /><strong>{rupiah(total)}</strong></div>
        <button className="button full" style={{ marginTop: 14 }} onClick={checkout}>Bayar dengan Midtrans</button>
        {message && <p className="muted">{message}</p>}
      </aside>
    </div>
  );
}
