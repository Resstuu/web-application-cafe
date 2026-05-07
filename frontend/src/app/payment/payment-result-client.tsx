"use client";

import { useSearchParams } from "next/navigation";
import PaymentStatus from "./payment-status";

export default function PaymentResultClient({ title, message }: { title: string; message: string }) {
  const params = useSearchParams();

  return <PaymentStatus code={params.get("order")} title={title} message={message} />;
}
