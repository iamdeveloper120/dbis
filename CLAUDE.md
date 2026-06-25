# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech Stack

- **Backend**: CodeIgniter 4 (PHP 8.1+), CodeIgniter Shield v1.1 for authentication
- **Frontend**: Server-rendered PHP views + a separate React 18 app (Vite, Axios, React-Select, SweetAlert2)
- **Database**: MySQL via MySQLi driver
- **PDF**: DOMPDF 3.1
- **Testing**: PHPUnit 10.5.16

## Commands

```bash
# PHP / backend
composer test                  # Run PHPUnit test suite
php spark <command>            # CodeIgniter CLI (migrations, cache, routes, etc.)
php spark routes               # List all registered routes

# React frontend (from /React/)
cd React && npm run dev        # Start Vite dev server
cd React && npm run build      # Production build
cd React && npm run lint       # ESLint
```

Run a single test file:
```bash
vendor/bin/phpunit tests/unit/DailyReportFormattingTest.php
```

Test logs are written to `build/logs/`.

## Architecture

### Controller / Route Organization

Routes are defined in `app/Config/Routes.php` with auto-routing disabled. All routes are grouped by domain (e.g., `/app-configuration`, `/client-configuration`, `/mands`), each mapped to a namespace under `App\Controllers\<Domain>`. Permission filters are applied per-route: `['filter' => 'permission:some.permission.key']`.

Controller namespaces mirror directories under `app/Controllers/`:
`Auth`, `AppConfiguration`, `ClientConfiguration`, `ClientProfile`, `ClientProgram`, `ClientSessions`, `ClientDailyData`, `ClientDataSheet`, `ClientGraphs`, `Mands`, `KPI`, `Reports`, `MasterProgram`, `UserConfiguration`, `Shared`.

### Model / Entity / Service Pattern

- **Models** (`app/Models/`) extend `CodeIgniter\Model` and handle database interaction only.
- **Entities** (`app/Entities/`) are typed DTOs hydrated from model results.
- **Services** (`app/Services/`) contain business logic. Key services:
  - `SessionProcessingService` — probe set phase management and stimulus chain compilation
  - `ClientDataSheetService` — data sheet aggregation
  - `ProbeSetProcessing/` — sub-service for probe processing
  - `Reports/` — report generation logic

### Authentication & Authorization

Shield handles session-based auth. `LoginController` extends Shield's built-in controller. Authorization is permission-based: permissions are stored per user/group and enforced via route filters. `app/Config/Filters.php` registers the `permission` filter alias.

### Views & Cells

Views are in `app/Views/` mirroring the controller structure. CodeIgniter View Cells (`app/Cells/`) are used for reusable view components.

### React Frontend

The `React/` directory is a standalone Vite app. It communicates with the CI4 backend via Axios JSON requests. Built assets are placed into `public/` for the PHP app to serve. React components are used for interactive UI slices (session data entry, graph views, program wizards).

### Database

No migrations are tracked; schema is maintained as raw SQL in `database/` (`dbis_schema_v1_tables.sql`, `dbis_schema_v1_views.sql`). Use `example.env` as the template for `.env` — it covers DB credentials, Redis, Memcached, email, and app settings.

### Helpers & Libraries

- `app/Helpers/custom_helper.php` — shared utility functions loaded globally
- `app/Helpers/report_helper.php` — report-specific formatting utilities
- `app/Libraries/Reports/` — report generation classes (PDF via DOMPDF)
- `app/Libraries/MandsOptionMetadata.php` — Mands module metadata
