"use client";

import { useEffect, useState } from "react";
import { getOrder, rupiah } from "@/lib/api";
import type { Order } from "@/lib/types";

export default function PaymentStatus({ code, title, message }: { code: string | null; title: string; message: string }) {
  const [order, setOrder] = useState<Order | null>(null);

  useEffect(() => {
    if (code) getOrder(code).then((res) => setOrder(res.data)).catch(() => setOrder(null));
  }, [code]);

  return (
    <section className="panel">
      <h1>{title}</h1>
      <p className="muted">{message}</p>
      {order && (
        <table>
          <tbody>
            <tr><th>Kode</th><td>{order.code}</td></tr>
            <tr><th>Pelanggan</th><td>{order.customer_name} - Meja {order.table_number}</td></tr>
            <tr><th>Status Order</th><td><span className="badge">{order.status}</span></td></tr>
            <tr><th>Status Bayar</th><td><span className="badge">{order.payment_status}</span></td></tr>
            <tr><th>Total</th><td>{rupiah(order.total)}</td></tr>
          </tbody>
        </table>
      )}
    </section>
  );
}
