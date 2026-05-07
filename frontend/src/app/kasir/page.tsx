"use client";

import { useEffect, useState } from "react";
import { apiFetch, getMenus, rupiah } from "@/lib/api";
import type { Menu, Order } from "@/lib/types";

export default function KasirPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [menus, setMenus] = useState<Menu[]>([]);
  const [message, setMessage] = useState("");

  async function load() {
    const [orderRes, menuRes] = await Promise.all([
      apiFetch<{ data: Order[] }>("/cashier/orders"),
      getMenus(),
    ]);
    setOrders(orderRes.data);
    setMenus(menuRes.data);
  }

  useEffect(() => { load().catch((err) => setMessage(err.message)); }, []);

  async function createOrder(form: FormData) {
    const items = menus
      .map((menu) => ({ menu_id: menu.id, qty: Number(form.get(`qty_${menu.id}`) ?? 0) }))
      .filter((item) => item.qty > 0);
    await apiFetch("/cashier/orders", {
      method: "POST",
      body: JSON.stringify({
        customer_name: form.get("customer_name"),
        table_number: form.get("table_number"),
        payment_status: form.get("payment_status"),
        items,
      }),
    });
    await load();
  }

  return (
    <div className="grid two" style={{ gridTemplateColumns: ".85fr 1.15fr" }}>
      <section className="panel">
        <h1>Kasir</h1>
        {message && <div className="error">{message}</div>}
        <form action={async (form) => { await createOrder(form); }}>
          <label>Nama pelanggan</label><input name="customer_name" required />
          <label>Nomor meja</label><input name="table_number" required />
          <label>Status bayar</label><select name="payment_status"><option value="belum_bayar">Belum bayar</option><option value="lunas">Lunas</option></select>
          <h3>Pilih Menu</h3>
          {menus.map((menu) => (
            <div className="row" key={menu.id} style={{ marginBottom: 8 }}>
              <span>{menu.name}</span><span className="spacer" /><input name={`qty_${menu.id}`} type="number" min={0} defaultValue={0} style={{ width: 84 }} />
            </div>
          ))}
          <button className="button full">Buat Pesanan</button>
        </form>
      </section>
      <section className="panel">
        <h2>Daftar Order</h2>
        <table><tbody>{orders.map((order) => (
          <tr key={order.id}>
            <td><strong>{order.code}</strong><br />{order.customer_name} - Meja {order.table_number}</td>
            <td>{order.items.map((item) => `${item.menu_name} x ${item.qty}`).join(", ")}</td>
            <td>{rupiah(order.total)}<br /><span className="badge">{order.status}</span> <span className="badge">{order.payment_status}</span></td>
            <td className="row">
              {order.payment_status !== "lunas" && <button className="button" onClick={async () => { await apiFetch(`/cashier/orders/${order.id}/paid`, { method: "PATCH" }); await load(); }}>Lunas</button>}
              {!["done", "cancelled"].includes(order.status) && <button className="button danger" onClick={async () => { await apiFetch(`/cashier/orders/${order.id}/cancel`, { method: "PATCH" }); await load(); }}>Batal</button>}
            </td>
          </tr>
        ))}</tbody></table>
      </section>
    </div>
  );
}
