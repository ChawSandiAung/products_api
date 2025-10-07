### Products API (Laravel)

A simple RESTful API for managing Products and Variants with Laravel, MySQL, Eloquent relationships, validation, API resources, and soft deletes.

#### Tech
- Laravel 12
- PHP 8.4
- MySQL 8.x (or compatible)

---

### Setup

1) Clone the repo
```bash
git clone <your-repo-url> products-api
cd products-api
```

2) Install dependencies and app key
```bash
composer install
cp .env.example .env
php artisan key:generate
```

3) Configure MySQL
- Create database and user:
```sql
CREATE DATABASE products_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'products_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON products_api.* TO 'products_user'@'localhost';
FLUSH PRIVILEGES;
```
- Update .env with DB credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=products_api
DB_USERNAME=products_user
DB_PASSWORD=strong_password_here
```
- Ensure PHP has pdo_mysql extension:
```bash
php -m | grep -i pdo_mysql || sudo apt-get install -y php-mysql
```

4) Migrate and seed
```bash
php artisan migrate --seed
```

5) Run the server
```bash
php artisan serve
```
Base URL: http://127.0.0.1:8000/api

---

### Architecture Overview

- Entities
  - Product: id, name, description, base_price, slug, timestamps, soft deletes
  - Variant: id, product_id, carat, metal_type (gold|white_gold|platinum), price, stock, sku, timestamps, soft deletes

- Relationships
  - Product hasMany Variant
  - Variant belongsTo Product

- Validation (Form Requests)
  - StoreProductRequest: validates create payloads (product + optional variants)
  - UpdateProductRequest: validates partial updates and variant upserts

- Serialization (API Resources)
  - ProductResource: formats product JSON, includes variants_count and optionally variants
  - VariantResource: formats variant JSON

- Controller
  - App\Http\Controllers\Api\ProductController
    - index: paginated products list with variants_count
    - show: product details including variants
    - store: transactional create (product + variants)
    - update: transactional update, upsert variants, optional soft delete subset
    - destroy: soft delete product and its variants

- Persistence
  - Migrations: products, variants (FK product_id), SoftDeletes
  - Factories: ProductFactory, VariantFactory
  - Seeders: ProductSeeder (registered in DatabaseSeeder)

- Error Handling & Status
  - 200 OK for reads/updates
  - 201 Created for store
  - 204 No Content for delete
  - 404 Not Found for missing resources
  - 422 Unprocessable Entity for validation errors

Optional (not required to run):
- Sanctum for auth (protect POST/PUT/DELETE)
- Example feature test for listing products

---

### API Endpoints

Base path: /api

- GET /products
  - Lists products with variants_count (paginated, default 10 per page)
  - Query params: page
  - Response 200 example:
    ```
    {
      "data": [
        {
          "id": 1,
          "name": "Elegant Ring",
          "description": "Lovely piece",
          "base_price": "199.00",
          "slug": "elegant-ring-abc12",
          "variants_count": 3,
          "created_at": "2025-10-07T09:40:00Z",
          "updated_at": "2025-10-07T09:40:00Z"
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 10
      }
    }
    ```

- GET /products/{id}
  - Shows a product with its variants
  - Response 200 example:
    ```
    {
      "id": 1,
      "name": "Elegant Ring",
      "description": "Lovely piece",
      "base_price": "199.00",
      "slug": "elegant-ring-abc12",
      "variants": [
        {
          "id": 11,
          "carat": "1.00",
          "metal_type": "gold",
          "price": "249.00",
          "stock": 5,
          "sku": "SKU-1234-AA",
          "created_at": "...",
          "updated_at": "..."
        }
      ],
      "created_at": "...",
      "updated_at": "..."
    }
    ```
  - 404 if not found

- POST /products
  - Creates a product with optional variants
  - Request example:
    ```
    {
      "name": "Classic Pendant",
      "description": "Timeless",
      "base_price": 150.00,
      "slug": "classic-pendant",
      "variants": [
        { "carat": 0.75, "metal_type": "gold", "price": 199.99, "stock": 10, "sku": "SKU-5678-BB" },
        { "carat": 1.00, "metal_type": "platinum", "price": 299.99, "stock": 3, "sku": "SKU-9012-CC" }
      ]
    }
    ```
  - 201 Created on success; 422 on validation error

- PUT /products/{id}
  - Updates product; upserts variants; optional soft-delete of variants by IDs
  - Request example:
    ```
    {
      "name": "Elegant Ring Updated",
      "variants": [
        { "id": 11, "carat": 1.25, "metal_type": "gold", "price": 259.99, "stock": 4, "sku": "SKU-1234-AA" },
        { "carat": 0.50, "metal_type": "white_gold", "price": 179.99, "stock": 8, "sku": "SKU-NEW-01" }
      ],
      "variants_delete": [12]
    }
    ```
  - 200 OK on success; 404 if product not found

- DELETE /products/{id}
  - Soft deletes the product and its variants
  - 204 No Content; 404 if not found

---

### Example cURL

- List products
```
curl http://127.0.0.1:8000/api/products
```

- Show product by ID
```
curl http://127.0.0.1:8000/api/products/1
```

- Create product
```
curl -X POST http://127.0.0.1:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Classic Pendant",
    "description": "Timeless",
    "base_price": 150.00,
    "slug": "classic-pendant",
    "variants": [
      { "carat": 0.75, "metal_type": "gold", "price": 199.99, "stock": 10, "sku": "SKU-5678-BB" },
      { "carat": 1.00, "metal_type": "platinum", "price": 299.99, "stock": 3, "sku": "SKU-9012-CC" }
    ]
  }'
```

- Update product
```
curl -X PUT http://127.0.0.1:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Elegant Ring Updated",
    "variants": [
      { "id": 11, "carat": 1.25, "metal_type": "gold", "price": 259.99, "stock": 4, "sku": "SKU-1234-AA" },
      { "carat": 0.50, "metal_type": "white_gold", "price": 179.99, "stock": 8, "sku": "SKU-NEW-01" }
    ],
    "variants_delete": [12]
  }'
```

- Delete product
```
curl -X DELETE http://127.0.0.1:8000/api/products/1
```

---

### Submission

Include these in your GitHub repo or ZIP:
- /app
- /routes/api.php
- /database (migrations, factories, seeders)
- README.md (this file)

The project should run with:
```
php artisan migrate --seed
php artisan serve
```

---

### Notes/Troubleshooting

- If /api endpoints return 404, ensure:
  - routes/api.php contains routes
  - app/Providers/RouteServiceProvider.php groups api.php with prefix('api')
  - config/app.php providers includes App\Providers\RouteServiceProvider::class
  - Clear caches: php artisan optimize:clear; php artisan route:clear
- For DB errors, verify .env credentials and that pdo_mysql is enabled.
- Soft deletes hide records by default; use withTrashed() in code to include them.
