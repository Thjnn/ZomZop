# ZomZop — Directory Tree (Essential files only)

This file shows a simplified project tree with only the important files and folders.

## Mermaid diagram

```mermaid
graph TD
  Z[ZomZop/]
  Z --> artisan
  Z --> composer.json
  Z --> package.json
  Z --> .env
  Z --> README.md
  Z --> public
  public --> index.php

  Z --> app
  app --> Models
  Models --> Branch.php
  Models --> User.php
  app --> Http
  Http --> Controllers
  Controllers --> Controller.php

  Z --> routes
  routes --> web.php

  Z --> resources
  resources --> css
  css --> app.css
  resources --> js
  js --> app.js
  resources --> views
  views --> layouts
  layouts --> app.blade.php
  views --> home.blade.php
  views --> pizza.blade.php
  views --> welcome.blade.php
  views --> category
  category --> show.blade.php
  views --> attendance
  attendance --> (missing: face-recognition.blade.php)

  Z --> database
  database --> migrations
  migrations --> 2026_05_26_061458_create_branches_table.php
  migrations --> 2026_05_26_075637_create_users_table.php
  migrations --> 2026_05_26_083928_create_attendances_table.php
```

## Plain tree (compact)

- ZomZop/
  - artisan
  - composer.json
  - package.json
  - .env
  - README.md
  - public/
    - index.php
  - app/
    - Models/
      - Branch.php
      - User.php
    - Http/
      - Controllers/
        - Controller.php
  - routes/
    - web.php
  - resources/
    - css/
      - app.css
    - js/
      - app.js
    - views/
      - layouts/
        - app.blade.php
      - home.blade.php
      - pizza.blade.php
      - welcome.blade.php
      - category/
        - show.blade.php
      - attendance/
        - (missing: face-recognition.blade.php)
  - database/
    - migrations/
      - 2026_05_26_061458_create_branches_table.php
      - 2026_05_26_075637_create_users_table.php
      - 2026_05_26_083928_create_attendances_table.php

---

File generated to provide a concise directory map for review or handoff.