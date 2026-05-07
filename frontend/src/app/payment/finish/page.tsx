import { Suspense } from "react";
import PaymentResultClient from "../payment-result-client";

export default function PaymentFinishPage() {
  return (
    <Suspense fallback={<section className="panel">Memuat status pembayaran...</section>}>
      <PaymentResultClient title="Pembayaran diproses" message="Jika notifikasi Midtrans sudah diterima, pesanan akan masuk ke Kitchen/Barista." />
    </Suspense>
  );
}
