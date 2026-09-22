# Product Management API

REST API untuk manajemen produk dan autentikasi — dibangun dengan Laravel, MySQL, Redis, dan Docker.

## Stack

- Laravel 13 (PHP 8.3)
- MySQL 8.0
- Redis (cache + rate limiting)
- tymon/jwt-auth (JWT authentication + refresh token)
- Docker & Docker Compose

## Menjalankan dengan Docker

```bash
git clone https://github.com/akbarardi/product_management_api_bts.git
cd product_management_api_bts

cp .env.example .env

docker-compose up -d --build

docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate
```

API akan berjalan di `http://localhost:8000`.

## Postman Collection

Import file `postman/product-management-api.postman_collection.json` ke Postman.

Collection sudah dikonfigurasi dengan:
- Variable `{{base_url}}` (default: `http://localhost:8000`)
- Auto-set `{{authentication_token}}` dari response login
- Bearer token otomatis pada endpoint yang membutuhkan auth

Urutan test: Register → Login → Create Product → List Products → Get Product → Update Product → Delete Product.

## Endpoint

### Auth
| Method | Endpoint | Deskripsi | Rate limit |
|---|---|---|---|
| POST | `/api/auth/register` | Registrasi user baru | 3x / 60 detik per IP |
| POST | `/api/auth/login` | Login, return `authentication_token` + `refresh_token` | 3x / 60 detik per IP |

### Products
| Method | Endpoint | Deskripsi | Auth | Rate limit |
|---|---|---|---|---|
| GET | `/api/products` | List produk, support `search`, `category`, `limit`, `page` | - | - |
| GET | `/api/products/:id` | Detail produk | - | - |
| POST | `/api/products` | Tambah produk
| PUT | `/api/products/:id` | Edit produk (partial update)
| DELETE | `/api/products/:id` | Hapus produk