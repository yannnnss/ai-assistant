# AI Workspace & Assistant

Chatbot web berbasis PHP dan JavaScript yang memakai Gemini API, dengan jawaban
yang mengalir secara *streaming* (SSE) dan riwayat percakapan yang disimpan ke MySQL.

## Fitur

- **Dua mode percakapan:**
  - 💬 *Chatbot general* — chatbot umum yang ramah dan solutif.
  - 💼 *Babu tugas* — asisten kerja: jawaban padat, jelas, dan efektif.
- **Streaming jawaban** — balasan AI tampil kata demi kata lewat Server-Sent Events,
  dirender sebagai Markdown (pustaka `marked.js`).
- **Riwayat tersimpan** — setiap percakapan disimpan ke tabel `chat_history` di MySQL.

## Cara kerja

- `index.php` — tampilan chat (pemilih mode, kotak pesan, kolom input).
- `script.js` — mengirim pesan ke `process.php`, membaca stream SSE, dan menampilkan
  jawaban secara bertahap.
- `process.php` — meneruskan prompt ke Gemini API (`streamGenerateContent`), lalu
  menyimpan pesan pengguna dan jawaban AI ke MySQL.
- `config.php` — kredensial database dan `GEMINI_API_KEY`.
- `style.css` — tampilan antarmuka.

## Menjalankan

Butuh: PHP (dengan ekstensi cURL) dan MySQL, misalnya lewat XAMPP atau
`php -S localhost:8000` dari folder proyek.

1. Isi kredensial MySQL dan `GEMINI_API_KEY` di `config.php`. Saat ini
   `GEMINI_API_KEY` masih kosong, jadi wajib diisi dulu sebelum dipakai.
2. Buat tabel riwayat di MySQL (kolomnya mengikuti `INSERT` di `process.php`):
   ```sql
   CREATE TABLE chat_history (
     id INT AUTO_INCREMENT PRIMARY KEY,
     mode VARCHAR(20),
     user_message TEXT,
     ai_response TEXT,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```
3. Jalankan server lalu buka `index.php` di browser.

## Catatan

- Model yang dipakai tertulis di `process.php` (`gemini-3.6-flash`) — pastikan nama
  modelnya masih benar di akun Gemini yang dipakai; kalau API mengembalikan error,
  periksa bagian ini dulu.
- `process.php` mematikan verifikasi SSL cURL (`CURLOPT_SSL_VERIFYPEER` dan
  `CURLOPT_SSL_VERIFYHOST` disetel `false`). Aman untuk pengembangan lokal, tapi
  jangan dipakai apa adanya di server publik.
