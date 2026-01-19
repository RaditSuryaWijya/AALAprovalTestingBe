# Contoh JSON Response API - Server-Driven UI

Dokumentasi ini menjelaskan struktur response JSON standar untuk endpoint list approval yang menggunakan konsep Server-Driven UI.

## Struktur Response Standar

Semua endpoint list approval mengembalikan struktur JSON yang sama:

```json
{
  "success": true,
  "config": {
    "page_title": "Judul Halaman",
    "mapping": {
      "title": "nama_field_title",
      "subtitle": "nama_field_subtitle",
      "date": "nama_field_date",
      "status": "nama_field_status"
    }
  },
  "data": [
    // Array data mentah dari database
  ]
}
```

## Kasus A: Approval PO (Purchase Order)

**Endpoint:** `GET /api/po`

**Response:**

```json
{
  "success": true,
  "config": {
    "page_title": "Daftar Purchase Order",
    "mapping": {
      "title": "nama_barang",
      "subtitle": "total_harga",
      "date": "created_at",
      "status": "status"
    }
  },
  "data": [
    {
      "id": 1,
      "creator_email": "staff@example.com",
      "nama_barang": "Laptop Dell XPS 15",
      "total_harga": "15000000.00",
      "status": "PENDING",
      "approver_email": null,
      "tgl_approve": null,
      "reject_reason": null,
      "created_at": "2024-01-08T10:30:00.000000Z",
      "updated_at": "2024-01-08T10:30:00.000000Z"
    },
    {
      "id": 2,
      "creator_email": "staff2@example.com",
      "nama_barang": "Monitor LG 27 inch",
      "total_harga": "3500000.00",
      "status": "APPROVED",
      "approver_email": "manager@example.com",
      "tgl_approve": "2024-01-08T11:00:00.000000Z",
      "reject_reason": null,
      "created_at": "2024-01-08T10:45:00.000000Z",
      "updated_at": "2024-01-08T11:00:00.000000Z"
    }
  ]
}
```

**Penjelasan Mapping PO:**
- `title` → `nama_barang`: Nama barang yang akan ditampilkan sebagai judul utama
- `subtitle` → `total_harga`: Total harga yang akan ditampilkan sebagai subtitle (bisa diformat sebagai currency)
- `date` → `created_at`: Tanggal pembuatan PO yang akan ditampilkan
- `status` → `status`: Status approval (PENDING, APPROVED, REJECTED)

---

## Kasus B: Approval Lembur

**Endpoint:** `GET /api/lembur`

**Response:**

```json
{
  "success": true,
  "config": {
    "page_title": "Daftar Approval Lembur",
    "mapping": {
      "title": "requestor_email",
      "subtitle": "keterangan",
      "date": "tanggal",
      "status": "status"
    }
  },
  "data": [
    {
      "id": 1,
      "requestor_email": "karyawan@example.com",
      "tanggal": "2024-01-10",
      "keterangan": "Lembur untuk menyelesaikan project deadline",
      "status": "PENDING_SPV",
      "spv_email": null,
      "tgl_approve_spv": null,
      "mgr_email": null,
      "tgl_approve_mgr": null,
      "reject_reason": null,
      "created_at": "2024-01-08T10:30:00.000000Z",
      "updated_at": "2024-01-08T10:30:00.000000Z"
    },
    {
      "id": 2,
      "requestor_email": "karyawan2@example.com",
      "tanggal": "2024-01-11",
      "keterangan": "Lembur untuk maintenance server",
      "status": "PENDING_MGR",
      "spv_email": "supervisor@example.com",
      "tgl_approve_spv": "2024-01-08T11:00:00.000000Z",
      "mgr_email": null,
      "tgl_approve_mgr": null,
      "reject_reason": null,
      "created_at": "2024-01-08T10:45:00.000000Z",
      "updated_at": "2024-01-08T11:00:00.000000Z"
    }
  ]
}
```

**Penjelasan Mapping Lembur:**
- `title` → `requestor_email`: Email karyawan yang request lembur (bisa ditampilkan sebagai nama karyawan jika ada relasi)
- `subtitle` → `keterangan`: Alasan/keterangan lembur yang akan ditampilkan sebagai subtitle
- `date` → `tanggal`: Tanggal lembur yang akan ditampilkan
- `status` → `status`: Status approval (PENDING_SPV, PENDING_MGR, APPROVED, REJECTED)

---

## Kasus C: Approval Cuti

**Endpoint:** `GET /api/cuti`

**Response:**

```json
{
  "success": true,
  "config": {
    "page_title": "Daftar Approval Cuti",
    "mapping": {
      "title": "requestor_email",
      "subtitle": "keterangan",
      "date": "tanggal",
      "status": "status"
    }
  },
  "data": [
    {
      "id": 1,
      "requestor_email": "karyawan@example.com",
      "tanggal": "2024-01-15",
      "keterangan": "Cuti tahunan",
      "status": "PENDING_SPV",
      "spv_email": null,
      "tgl_approve_spv": null,
      "mgr_email": null,
      "tgl_approve_mgr": null,
      "reject_reason": null,
      "created_at": "2024-01-08T10:30:00.000000Z",
      "updated_at": "2024-01-08T10:30:00.000000Z"
    }
  ]
}
```

**Penjelasan Mapping Cuti:**
- `title` → `requestor_email`: Email karyawan yang request cuti
- `subtitle` → `keterangan`: Keterangan/alasan cuti
- `date` → `tanggal`: Tanggal cuti
- `status` → `status`: Status approval (PENDING_SPV, PENDING_MGR, APPROVED, REJECTED)

---

## Cara Frontend Menggunakan Mapping

Frontend dapat menggunakan mapping ini untuk merender UI secara dinamis:

```javascript
// Contoh penggunaan di frontend (React/Flutter)
const renderItem = (item) => {
  const title = item[response.config.mapping.title];
  const subtitle = item[response.config.mapping.subtitle];
  const date = item[response.config.mapping.date];
  const status = item[response.config.mapping.status];
  
  return (
    <ListItem>
      <Title>{title}</Title>
      <Subtitle>{subtitle}</Subtitle>
      <Date>{formatDate(date)}</Date>
      <Status>{status}</Status>
    </ListItem>
  );
};
```

Dengan cara ini, frontend tidak perlu hardcode field name untuk setiap endpoint. Semua endpoint menggunakan struktur yang sama, hanya mapping-nya yang berbeda sesuai dengan field data yang tersedia.
