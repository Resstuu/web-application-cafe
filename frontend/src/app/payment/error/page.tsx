import { Suspense } from "react";
import PaymentResultClient from "../payment-result-client";

export default function PaymentErrorPage() {
  return (
    <Suspense fallback={<section className="panel">Memuat status pembayaran...</section>}>
      <PaymentResultClient title="Pembayaran gagal" message="Pembayaran gagal atau dibatalkan." />
    </Suspense>
  );
}
