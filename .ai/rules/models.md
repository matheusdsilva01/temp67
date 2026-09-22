---
paths:
  - 'app/{Models/User.php,Filament/**,Actions/DeleteFileAction.php}'
---

# Models

## Restrict administration to explicit administrators
The Filament admin panel accepts only users with users.is_admin=true via User::canAccessPanel(). File management is read-only except for deletion, which must remove storage content before deleting metadata.

## Restrict administration to explicit administrators
The Filament admin panel accepts only users with users.is_admin=true via User::canAccessPanel(). File management permits uploads through the Files page header action and deletion; both reuse domain actions. Deletion must remove storage content before deleting metadata.

## Admin file management permits uploads
This supersedes the earlier read-only restriction. Administrators may upload files from the Files page and delete existing files; uploads and deletions must reuse their domain actions.
