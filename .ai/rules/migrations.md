---
paths:
  - 'app/{Models/File.php,Http/Controllers/FileController.php,Actions/StoreFileAction.php}|routes/web.php|database/migrations/*files*'
---

# Migrations

## Use a separate public UUID for file links
File links resolve by the unique `files.public_id` UUID, never by the model primary key and never by signed URL query parameters. Generate a UUID v4 when creating a file; keep the internal `id` and randomized storage path private. Access remains valid only until `expires_at`.
