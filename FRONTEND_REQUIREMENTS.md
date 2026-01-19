# Dokumentasi Frontend Requirements

## Overview
Frontend perlu dibuat untuk sinkron dengan API Backend yang sudah ada. Dokumen ini menjelaskan semua komponen, halaman, dan integrasi yang diperlukan.

---

## 1. Authentication & Authorization

### 1.1 Login Page
**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "andi@kantor.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Andi",
      "email": "andi@kantor.com",
      "jabatan": "Staff",
      "department": "IT"
    },
    "token": "abc123xyz..."
  }
}
```

**Yang perlu dibuat:**
- Form login dengan field email & password
- Validasi form (email required, password required)
- Error handling untuk kredensial salah
- Simpan token ke localStorage/sessionStorage
- Redirect berdasarkan jabatan setelah login sukses

### 1.2 Logout
**Endpoint:** `POST /api/logout`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

**Yang perlu dibuat:**
- Button/logout function
- Hapus token dari storage
- Redirect ke halaman login

### 1.3 Get Current User
**Endpoint:** `GET /api/me`
**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Andi",
    "email": "andi@kantor.com",
    "jabatan": "Staff",
    "department": "IT"
  }
}
```

**Yang perlu dibuat:**
- Function untuk get user info saat app load
- Simpan user info ke state/context
- Gunakan untuk menampilkan nama user di header/navbar

---

## 2. Dashboard & Navigation

### 2.1 Dashboard Layout
**Yang perlu dibuat:**
- Sidebar/Navbar dengan menu sesuai jabatan:
  - **Staff:** 
    - Dashboard
    - Request Lembur (Buat Baru)
    - Request PO (Buat Baru)
    - History Lembur Saya
    - History PO Saya
  - **Supervisor:**
    - Dashboard
    - Approval Lembur (Pending)
    - History Approval Lembur
  - **Manager:**
    - Dashboard
    - Approval Lembur (Pending)
    - Approval PO (Pending)
    - History Approval Lembur
    - History Approval PO

### 2.2 Protected Routes
**Yang perlu dibuat:**
- Route guard/Protected route wrapper
- Cek token di localStorage
- Redirect ke login jika tidak ada token
- Cek token valid dengan `/api/me`

---

## 3. Module Lembur (Overtime)

### 3.1 List Lembur (Index)
**Endpoint:** `GET /api/lembur`
**Headers:** `Authorization: Bearer {token}`

**Response (berbeda berdasarkan jabatan):**

**Staff:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "requestor_email": "andi@kantor.com",
      "tanggal": "2024-01-15",
      "keterangan": "Lembur untuk maintenance server",
      "status": "PENDING_SPV",
      "spv_email": null,
      "tgl_approve_spv": null,
      "mgr_email": null,
      "tgl_approve_mgr": null,
      "reject_reason": null,
      "created_at": "2024-01-08T00:50:27.000000Z",
      "updated_at": "2024-01-08T00:50:27.000000Z"
    }
  ]
}
```

**Supervisor:**
- Hanya melihat status `PENDING_SPV`

**Manager:**
- Hanya melihat status `PENDING_MGR`

**Yang perlu dibuat:**
- Table/List component untuk menampilkan data
- Filter berdasarkan status (jika perlu)
- Badge/Status indicator (PENDING_SPV, PENDING_MGR, APPROVED, REJECTED)
- Action buttons sesuai role:
  - Staff: View detail, Edit (jika PENDING_SPV), Cancel
  - Supervisor: Approve, Reject
  - Manager: Approve, Reject

### 3.2 Create Lembur Request
**Endpoint:** `POST /api/lembur`
**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "tanggal": "2024-01-15",
  "keterangan": "Lembur untuk maintenance server"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Request lembur berhasil dibuat",
  "data": {
    "id": 1,
    "requestor_email": "andi@kantor.com",
    "tanggal": "2024-01-15",
    "keterangan": "Lembur untuk maintenance server",
    "status": "PENDING_SPV",
    ...
  }
}
```

**Yang perlu dibuat:**
- Form create lembur:
  - Field tanggal (date picker)
  - Field keterangan (textarea)
  - Validasi: tanggal required, keterangan required
  - Submit button
- Success notification
- Redirect ke list setelah sukses

### 3.3 Detail Lembur
**Endpoint:** `GET /api/lembur/{id}`
**Headers:** `Authorization: Bearer {token}`

**Response:** Sama seperti item di list

**Yang perlu dibuat:**
- Detail page/modal untuk melihat info lengkap
- Tampilkan timeline approval:
  - Request dibuat oleh: {requestor_email} pada {created_at}
  - Supervisor approve: {spv_email} pada {tgl_approve_spv} (jika ada)
  - Manager approve: {mgr_email} pada {tgl_approve_mgr} (jika ada)
  - Reject reason (jika status REJECTED)

### 3.4 Approve Lembur (Supervisor)
**Endpoint:** `POST /api/lembur/{id}/approve-supervisor`
**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "action": "approve" // atau "reject"
}
```

**Atau untuk reject:**
```json
{
  "action": "reject",
  "reject_reason": "Budget tidak mencukupi"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Request lembur berhasil diapprove oleh Supervisor",
  "data": { ... }
}
```

**Yang perlu dibuat:**
- Modal/Confirmation dialog untuk approve
- Form untuk reject (dengan field reject_reason)
- Success notification
- Refresh list setelah approve/reject

### 3.5 Approve Lembur (Manager)
**Endpoint:** `POST /api/lembur/{id}/approve-manager`
**Headers:** `Authorization: Bearer {token}`

**Request:** Sama seperti supervisor

**Yang perlu dibuat:**
- Sama seperti supervisor approval
- Perhatikan: Manager hanya bisa approve yang statusnya `PENDING_MGR`

---

## 4. Module PO (Purchase Order)

### 4.1 List PO
**Endpoint:** `GET /api/po`
**Headers:** `Authorization: Bearer {token}`

**Response:**

**Staff:**
- Hanya melihat PO mereka sendiri

**Manager:**
- Melihat semua PO dengan status `PENDING`

**Yang perlu dibuat:**
- Table/List component
- Format currency untuk total_harga
- Status badge
- Action buttons:
  - Staff: View detail, Edit (jika PENDING), Cancel
  - Manager: Approve, Reject

### 4.2 Create PO Request
**Endpoint:** `POST /api/po`
**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "nama_barang": "Laptop Dell XPS 15",
  "total_harga": 25000000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Request PO berhasil dibuat",
  "data": {
    "id": 1,
    "creator_email": "andi@kantor.com",
    "nama_barang": "Laptop Dell XPS 15",
    "total_harga": "25000000.00",
    "status": "PENDING",
    ...
  }
}
```

**Yang perlu dibuat:**
- Form create PO:
  - Field nama_barang (text input)
  - Field total_harga (number input dengan format currency)
  - Validasi: nama_barang required, total_harga required & > 0
  - Submit button
- Success notification
- Redirect ke list setelah sukses

### 4.3 Detail PO
**Endpoint:** `GET /api/po/{id}`
**Headers:** `Authorization: Bearer {token}`

**Yang perlu dibuat:**
- Detail page/modal
- Tampilkan timeline approval
- Format currency untuk total_harga

### 4.4 Approve PO (Manager)
**Endpoint:** `POST /api/po/{id}/approve`
**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
  "action": "approve" // atau "reject"
}
```

**Atau untuk reject:**
```json
{
  "action": "reject",
  "reject_reason": "Budget tidak mencukupi"
}
```

**Yang perlu dibuat:**
- Modal/Confirmation dialog
- Form untuk reject dengan reject_reason
- Success notification
- Refresh list setelah approve/reject

---

## 5. State Management & API Integration

### 5.1 API Service/Client
**Yang perlu dibuat:**
- Axios instance atau fetch wrapper dengan:
  - Base URL: `http://your-api-url/api`
  - Interceptor untuk menambahkan token ke header
  - Error handling untuk 401 (unauthorized) → redirect ke login
  - Error handling untuk 403 (forbidden)
  - Error handling untuk 500 (server error)

**Contoh struktur:**
```javascript
// apiClient.js
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
});

// Request interceptor
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### 5.2 Auth Context/Store
**Yang perlu dibuat:**
- State untuk:
  - `user` (user info)
  - `token` (auth token)
  - `isAuthenticated` (boolean)
- Functions:
  - `login(email, password)`
  - `logout()`
  - `getCurrentUser()`
  - `checkAuth()`

### 5.3 Data Context/Store
**Yang perlu dibuat:**
- State untuk:
  - `lemburList` (array)
  - `poList` (array)
  - `loading` (boolean)
  - `error` (string/null)
- Functions:
  - `fetchLemburList()`
  - `fetchPoList()`
  - `createLembur(data)`
  - `createPo(data)`
  - `approveLembur(id, action, reason?)`
  - `approvePo(id, action, reason?)`

---

## 6. UI Components yang Perlu Dibuat

### 6.1 Common Components
- **Button:** Primary, Secondary, Danger variants
- **Input:** Text, Email, Password, Date, Number, Textarea
- **Modal/Dialog:** Untuk confirmations dan forms
- **Table:** Untuk list data dengan pagination (jika perlu)
- **Badge:** Untuk status indicators
- **Loading Spinner:** Untuk loading states
- **Toast/Notification:** Untuk success/error messages
- **Card:** Untuk container content
- **Navbar/Sidebar:** Navigation component

### 6.2 Status Badge Colors
- `PENDING_SPV`: Yellow/Orange
- `PENDING_MGR`: Blue
- `PENDING`: Yellow/Orange
- `APPROVED`: Green
- `REJECTED`: Red

### 6.3 Form Validation
- Client-side validation sebelum submit
- Error messages di bawah setiap field
- Disable submit button saat loading

---

## 7. Routing Structure

### 7.1 Public Routes
- `/login` - Login page

### 7.2 Protected Routes (Staff)
- `/dashboard` - Dashboard
- `/lembur` - List lembur saya
- `/lembur/create` - Buat request lembur
- `/lembur/:id` - Detail lembur
- `/po` - List PO saya
- `/po/create` - Buat request PO
- `/po/:id` - Detail PO

### 7.3 Protected Routes (Supervisor)
- `/dashboard` - Dashboard
- `/lembur/approval` - List pending approval lembur
- `/lembur/:id` - Detail lembur

### 7.4 Protected Routes (Manager)
- `/dashboard` - Dashboard
- `/lembur/approval` - List pending approval lembur
- `/lembur/:id` - Detail lembur
- `/po/approval` - List pending approval PO
- `/po/:id` - Detail PO

---

## 8. Error Handling

### 8.1 Error Scenarios
- **401 Unauthorized:** Token invalid/expired → Redirect ke login
- **403 Forbidden:** User tidak punya akses → Show error message
- **404 Not Found:** Data tidak ditemukan → Show error message
- **422 Validation Error:** Show validation errors di form
- **500 Server Error:** Show generic error message

### 8.2 Error Messages
- Buat component untuk menampilkan error messages
- Toast notification untuk error
- Inline error di form fields

---

## 9. Testing Scenarios

### 9.1 Test Cases yang Perlu Diuji
1. **Login:**
   - Login dengan kredensial benar
   - Login dengan kredensial salah
   - Token disimpan dengan benar

2. **Staff Flow:**
   - Buat request lembur → Status PENDING_SPV
   - Buat request PO → Status PENDING
   - Lihat history request sendiri

3. **Supervisor Flow:**
   - Login sebagai Supervisor
   - Hanya melihat lembur dengan status PENDING_SPV
   - Tidak melihat PO
   - Approve lembur → Status berubah ke PENDING_MGR
   - Reject lembur → Status berubah ke REJECTED

4. **Manager Flow:**
   - Login sebagai Manager
   - Melihat lembur dengan status PENDING_MGR
   - Melihat PO dengan status PENDING
   - Approve lembur → Status berubah ke APPROVED
   - Approve PO → Status berubah ke APPROVED

---

## 10. Additional Features (Optional)

### 10.1 Real-time Updates
- WebSocket atau polling untuk update real-time
- Notifikasi saat ada request baru (untuk Supervisor/Manager)

### 10.2 Filter & Search
- Filter berdasarkan status
- Filter berdasarkan tanggal
- Search by keyword

### 10.3 Export/Print
- Export list ke PDF/Excel
- Print detail request

### 10.4 Dashboard Statistics
- Total request per status
- Chart untuk visualisasi data
- Summary statistics

---

## 11. Tech Stack Recommendations

### 11.1 Framework Options
- **React** + React Router + Context API/Redux
- **Vue.js** + Vue Router + Vuex/Pinia
- **Next.js** (React framework)
- **Nuxt.js** (Vue framework)

### 11.2 UI Library Options
- **Material-UI** (MUI)
- **Ant Design**
- **Tailwind CSS** + Headless UI
- **Chakra UI**
- **Bootstrap**

### 11.3 HTTP Client
- **Axios** (recommended)
- **Fetch API**

---

## 12. Checklist Implementation

### Phase 1: Setup & Authentication
- [ ] Setup project structure
- [ ] Setup routing
- [ ] Create API client/service
- [ ] Create Auth context/store
- [ ] Create Login page
- [ ] Create Protected route wrapper
- [ ] Test authentication flow

### Phase 2: Staff Features
- [ ] Create Lembur list page
- [ ] Create Lembur form (create)
- [ ] Create Lembur detail page
- [ ] Create PO list page
- [ ] Create PO form (create)
- [ ] Create PO detail page
- [ ] Test Staff flow

### Phase 3: Supervisor Features
- [ ] Create Approval Lembur list page
- [ ] Create Approval modal/dialog
- [ ] Test Supervisor flow

### Phase 4: Manager Features
- [ ] Create Approval Lembur list page (Manager)
- [ ] Create Approval PO list page
- [ ] Create Approval modals
- [ ] Test Manager flow

### Phase 5: Polish & Testing
- [ ] Add loading states
- [ ] Add error handling
- [ ] Add notifications/toasts
- [ ] Responsive design
- [ ] Cross-browser testing
- [ ] End-to-end testing

---

## 13. API Endpoints Summary

### Authentication
- `POST /api/login` - Login
- `POST /api/logout` - Logout (protected)
- `GET /api/me` - Get current user (protected)

### Lembur
- `GET /api/lembur` - List lembur (protected)
- `POST /api/lembur` - Create lembur (protected)
- `GET /api/lembur/{id}` - Detail lembur (protected)
- `POST /api/lembur/{id}/approve-supervisor` - Approve by Supervisor (protected)
- `POST /api/lembur/{id}/approve-manager` - Approve by Manager (protected)

### PO
- `GET /api/po` - List PO (protected)
- `POST /api/po` - Create PO (protected)
- `GET /api/po/{id}` - Detail PO (protected)
- `POST /api/po/{id}/approve` - Approve PO (protected)

---

## Catatan Penting

1. **Token Management:** Simpan token dengan aman, jangan hardcode
2. **Error Handling:** Handle semua error scenario dengan baik
3. **Loading States:** Tampilkan loading indicator saat fetch data
4. **Validation:** Validasi di client-side dan handle server-side validation
5. **Security:** Jangan expose token di console.log
6. **Responsive:** Pastikan UI responsive untuk mobile & desktop
7. **Accessibility:** Pertimbangkan a11y untuk better UX

---

**Selamat mengembangkan Frontend! 🚀**

