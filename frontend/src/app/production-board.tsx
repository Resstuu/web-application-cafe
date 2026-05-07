"use client";

import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import type { Order } from "@/lib/types";

export default function ProductionBoard({ category, title }: { category: "makanan" | "minuman"; title: string }) {
  const [orders, setOrders] = useState<Order[]>([]);
  const [message, setMessage] = useState("");

  async function load() {
    const res = await apiFetch<{ data: Order[] }>(`/production/${category}`);
    setOrders(res.data);
  }

  useEffect(() => {
    load().catch((err) => setMessage(err.message));
    const timer = window.setInterval(() => load().catch((err) => setMessage(err.message)), 12000);
    return () => window.clearInterval(timer);
  }, [category]);

  async function complete(order: Order) {
    await apiFetch(`/production/${order.id}/${category}/complete`, { method: "PATCH" });
    await load();
  }

  return (
    <section>
      <div className="row" style={{ marginBottom: 16 }}>
        <h1 style={{ margin: 0 }}>{title}</h1>
        <span className="spacer" />
        <span className="badge">Auto refresh 12 detik</span>
      </div>
      {message && <div className="error">{message}</div>}
      <div className="grid two">
        {orders.length === 0 && (
          <div className="panel">
            <h2>Tidak ada pesanan aktif.</h2>
            <p className="muted">Pesanan baru akan muncul setelah pembayaran sukses atau kasir membuat order manual.</p>
          </div>
        )}
        {orders.map((order) => (
          <article className="card" key={order.id}>
            <div className="row">
              <h2 style={{ margin: 0 }}>{order.customer_name}</h2>
              <span className="spacer" />
              <span className="badge">Meja {order.table_number}</span>
            </div>
            <p className="muted" style={{ margin: 0 }}>{order.code}</p>
            <h3>{order.items.map((item) => `${item.menu_name} x ${item.qty}`).join(", ")}</h3>
            <button className="button full" onClick={() => complete(order)}>Selesai</button>
          </article>
        ))}
      </div>
    </section>
  );
}
