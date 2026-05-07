import { Suspense } from "react";
import PaymentResultClient from "../payment-result-client";

export default function PaymentUnfinishPage() {
  return (
    <Suspense fallback={<section className="panel">Memuat status pembayaran...</section>}>
      <PaymentResultClient title="Pembayaran belum selesai" message="Silakan lanjutkan pembayaran dari halaman Midtrans." />
    </Suspense>
  );
}
