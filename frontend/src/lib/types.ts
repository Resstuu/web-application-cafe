export type Role = "super_admin" | "kasir" | "kitchen" | "barista";

export type User = {
  id: number;
  name: string;
  email: string;
  role: Role;
  is_active: boolean;
};

export type Menu = {
  id: number;
  name: string;
  category: "makanan" | "minuman";
  price: number;
  description: string | null;
  is_active: boolean;
};

export type OrderItem = {
  id: number;
  menu_id: number | null;
  menu_name: string;
  category: "makanan" | "minuman";
  price: number;
  qty: number;
  status: "waiting" | "done";
};

export type Order = {
  id: number;
  code: string;
  customer_name: string;
  table_number: string;
  source: "customer" | "kasir";
  status: "pending_payment" | "confirmed" | "partially_done" | "done" | "cancelled" | "payment_failed";
  payment_status: "belum_bayar" | "pending" | "lunas" | "gagal";
  total: number;
  items: OrderItem[];
};
