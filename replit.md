# Workspace

## Overview

pnpm workspace monorepo using TypeScript. PBX Call Center Management Dashboard for a Nigeria-based ISP, including call queue management, IVR menus, ring groups, extensions, SLA escalation rules, call recording, live call monitoring, and analytics reporting.

## Stack

- **Monorepo tool**: pnpm workspaces
- **Node.js version**: 24
- **Package manager**: pnpm
- **TypeScript version**: 5.9
- **API framework**: Express 5
- **Database**: PostgreSQL + Drizzle ORM
- **Validation**: Zod (`zod/v4`), `drizzle-zod`
- **API codegen**: Orval (from OpenAPI spec)
- **Build**: esbuild (CJS bundle)
- **Frontend**: React + Vite, Tailwind CSS, Recharts, TanStack Query
- **Auth**: Replit Auth (OIDC/PKCE) via `openid-client`
- **WebSockets**: `ws` package for live call monitoring

## PHP Rewrite — `artifacts/pbx-php/`

The dashboard has been **completely rewritten** in PHP 8.4 with Bootstrap 5 UI, targeting cPanel/MySQL hosting. The `pbx-dashboard` artifact runs the PHP app.

### PHP App Architecture
- **Server**: PHP 8.4 built-in server (`php -S 0.0.0.0:$PORT`) on port 5000
- **Web root**: `artifacts/pbx-php/public/` — `index.php` is the front controller/router
- **Database**: PDO dual-driver — auto-detects PostgreSQL (`DATABASE_URL` env, Replit dev) or MySQL (cPanel credentials in `config.php`)
- **Auth**: Session-based (email + bcrypt via `dashboard_users.password_hash` column)
- **Settings**: `src/Settings.php` — key-value store in `system_settings` table; groups: `general`, `freepbx`, `ami`, `whatsapp`, `smtp`. Sensitive values never returned verbatim.
- **WhatsApp**: Meta Cloud API v19.0 integration; webhook at `/pbx-api/whatsapp-webhook` (GET=verify, POST=events); team inbox at `/whatsapp`; tables: `whatsapp_conversations`, `whatsapp_messages`
- **JavaScript load order**: Bootstrap JS + app.js are in `<head>` so page inline scripts can call `api()` / `openModal()` immediately on parse
- **API prefix**: PHP API endpoints are at `/pbx-api/*` (NOT `/api/*`). The Replit proxy routes `/api/*` to the separate Express API Server artifact on port 8080; using `/pbx-api/` avoids that conflict. The `api()` JS helper in app.js constructs `BASE+'/pbx-api'+path`.
- **Charts**: Chart.js 4.x (CDN)
- **Frontend**: Vanilla JS + AJAX (fetch API), Bootstrap 5.3 + Bootstrap Icons (CDN)
- **CSS**: Bootstrap 5.3 dark theme (`data-bs-theme="dark"`) + custom dark navy sidebar overrides in `style.css`
- **cPanel deployment**: Import `schema.sql` into MySQL, set credentials in `src/config.php`

### Default login credentials
- **Email**: `admin@pbx.local`
- **Password**: `Admin123!`

### PHP App Structure
```text
artifacts/pbx-php/
├── public/
│   ├── index.php          (front controller + router)
│   └── assets/
│       ├── css/style.css  (dark navy NOC CSS)
│       └── js/app.js      (AJAX, modals, Chart.js defaults, polling)
├── src/
│   ├── config.php         (DATABASE_URL parsing, constants)
│   ├── Database.php       (PDO singleton with query/row/execute/insert helpers)
│   ├── helpers.php        (formatDuration, statusBadge, h(), json_response)
│   ├── auth.php           (login/logout/session guard: require_auth, require_auth_api)
│   ├── layout.php         (sidebar + topbar HTML template)
│   ├── _confirm-modal.php (reusable delete confirm dialog)
│   └── pages/             (15 page files — dashboard, extensions, call-logs, etc.)
└── api/                   (15 AJAX endpoint files returning JSON)
```

### Pages
| URL | Page |
|-----|------|
| `/` or `/dashboard` | Overview (KPI cards + charts + agent table) |
| `/live-monitor` | Live active calls (polling every 10s) |
| `/extensions` | Extensions CRUD |
| `/call-queues` | Call Queues CRUD |
| `/ring-groups` | Ring Groups CRUD |
| `/ivr-menus` | IVR Menus CRUD |
| `/sla-rules` | SLA Rules CRUD |
| `/time-conditions` | Time Conditions CRUD |
| `/call-logs` | Paginated call log table with search |
| `/reports` | Analytics with date picker, charts, agent table |
| `/sip-trunks` | SIP Trunks CRUD |
| `/pbx-agents` | PBX Agents CRUD |
| `/dashboard-users` | Dashboard User Management CRUD |
| `/settings` | FreePBX integration settings |

---

## Structure

```text
artifacts-monorepo/
├── artifacts/              # Deployable applications
│   ├── api-server/         # Express API server (port 8080)
│   ├── pbx-php/            # PHP 8.4 dashboard app (served by pbx-dashboard artifact)
│   └── pbx-dashboard/      # Artifact config — now runs PHP server on port 5000
├── lib/                    # Shared libraries
│   ├── api-spec/           # OpenAPI spec + Orval codegen config
│   ├── api-client-react/   # Generated React Query hooks
│   ├── api-zod/            # Generated Zod schemas from OpenAPI
│   ├── db/                 # Drizzle ORM schema + DB connection
│   └── replit-auth-web/    # useAuth hook for Replit Auth on web
├── scripts/                # Utility scripts
├── pnpm-workspace.yaml     # pnpm workspace config
├── tsconfig.base.json      # Shared TS options
├── tsconfig.json           # Root TS project references
└── package.json            # Root package with hoisted devDeps
```

## TypeScript & Composite Projects

Every package extends `tsconfig.base.json` which sets `composite: true`. The root `tsconfig.json` lists all packages as project references. This means:

- **Always typecheck from the root** — run `pnpm run typecheck` (which runs `tsc --build --emitDeclarationOnly`). This builds the full dependency graph so that cross-package imports resolve correctly.
- **`emitDeclarationOnly`** — we only emit `.d.ts` files during typecheck; actual JS bundling is handled by esbuild/tsx/vite.
- **Project references** — when package A depends on package B, A's `tsconfig.json` must list B in its `references` array.
- **`replit-auth-web`** is resolved via path mapping in pbx-dashboard tsconfig (`@workspace/replit-auth-web` → `../../lib/replit-auth-web/src/index.ts`) since it exports source TypeScript directly.

## Root Scripts

- `pnpm run build` — runs `typecheck` first, then recursively runs `build` in all packages that define it
- `pnpm run typecheck` — runs `tsc --build --emitDeclarationOnly` using project references

## API Routes

All routes are prefixed with `/api` (mounted via the artifact routing system):

| Route | Description |
|-------|-------------|
| `GET /api/health` | Health check |
| `GET /api/auth/user` | Returns current logged-in user or null |
| `GET /api/login` | Initiates Replit OIDC login |
| `GET /api/callback` | OIDC callback handler |
| `GET /api/logout` | Logout + redirect |
| `GET /api/extensions` | List extensions |
| `POST /api/extensions` | Create extension |
| `PUT /api/extensions/:id` | Update extension |
| `DELETE /api/extensions/:id` | Delete extension |
| `GET /api/ring-groups` | List ring groups |
| `GET /api/ivr-menus` | List IVR menus |
| `GET /api/call-logs` | Paginated call logs (filter by status) |
| `GET /api/call-logs/:id/recording` | Redirect to call recording URL |
| `GET /api/time-conditions` | List time conditions |
| `GET /api/call-queues` | List call queues |
| `POST /api/call-queues` | Create call queue |
| `PUT /api/call-queues/:id` | Update call queue |
| `DELETE /api/call-queues/:id` | Delete call queue |
| `GET /api/sla-rules` | List SLA/escalation rules |
| `POST /api/sla-rules` | Create SLA rule |
| `PUT /api/sla-rules/:id` | Update SLA rule |
| `DELETE /api/sla-rules/:id` | Delete SLA rule |
| `GET /api/reports/daily-summary` | Daily call volume summary (from/to params) |
| `GET /api/reports/agent-performance` | Per-agent stats (from/to params) |
| `GET /api/reports/ivr-distribution` | IVR key selection distribution |
| `GET /api/active-calls` | Current in-memory active calls |
| `POST /api/active-calls` | Simulate a new call |
| `DELETE /api/active-calls/:callId` | End/remove an active call |
| `WS /ws` | WebSocket for live call events (snapshot, call_added, call_updated, call_ended) |

## Frontend Pages

| Route | Page | Description |
|-------|------|-------------|
| `/` | Overview | Dashboard: KPI cards, call volume chart, extension status |
| `/extensions` | Extensions | CRUD for PBX extensions |
| `/ivr` | IVR Menus | IVR menu management |
| `/ring-groups` | Ring Groups | Ring group management |
| `/call-logs` | Call Logs | Paginated call log table with play button for recordings |
| `/time-conditions` | Time Conditions | Business hours routing rules |
| `/queues` | Call Queues | Call queue CRUD (strategy, members, timeout) |
| `/live-monitor` | Live Monitor | Real-time WebSocket active call monitor with simulate button |
| `/sla-rules` | SLA Rules | Escalation rule CRUD with enable/disable toggles |
| `/reports` | Reports | Date-range analytics: daily volume, agent performance, IVR distribution |

## Database Tables

| Table | Description |
|-------|-------------|
| `users` | Auth users (Replit OIDC) |
| `sessions` | Server-side session store |
| `extensions` | PBX extensions |
| `ring_groups` | Ring group definitions |
| `ivr_menus` | IVR menus with options |
| `call_logs` | Call history with recording URLs |
| `time_conditions` | Business hours rules |
| `call_queues` | Call queue configs |
| `sla_rules` | Escalation/SLA rules |

## Auth

Auth uses Replit OIDC (openid-client). Session stored in PostgreSQL `sessions` table. User data in `users` table. The `authMiddleware.ts` populates `req.user` and `req.isAuthenticated()`. Currently auth is wired in but not enforcing protection on all routes (the `/api/auth/user` endpoint returns null if unauthenticated, and the frontend sidebar shows "Admin Login" to trigger auth).

## WebSocket Live Monitor

`lib/activeCallsStore.ts` holds the in-memory active call state and broadcasts events to all connected WebSocket clients. The WebSocket server is initialized on the same HTTP server as Express. WS path is `/ws` — added to the API artifact's routing paths so the proxy forwards WS upgrades correctly.

## Packages

### `artifacts/api-server` (`@workspace/api-server`)

Express 5 API server with WebSocket support.

- Entry: `src/index.ts` — creates HTTP server, initializes WebSocket, listens on `PORT`
- App setup: `src/app.ts` — mounts CORS, JSON/urlencoded, cookie parser, session, auth middleware, routes at `/api`
- Routes: `src/routes/index.ts` mounts all sub-routers
- Depends on: `@workspace/db`, `@workspace/api-zod`, `ws`, `openid-client`

### `lib/db` (`@workspace/db`)

Database layer using Drizzle ORM with PostgreSQL.

- `src/schema/index.ts` — barrel re-export of all models
- Tables: auth (sessions, users), extensions, ring-groups, ivr-menus, call-logs, time-conditions, call-queues, sla-rules
- Run `pnpm --filter @workspace/db run push` to sync schema

### `lib/api-spec` (`@workspace/api-spec`)

Owns the OpenAPI 3.1 spec (`openapi.yaml`) and the Orval config (`orval.config.ts`).

Run codegen: `pnpm --filter @workspace/api-spec run codegen`

### `lib/replit-auth-web` (`@workspace/replit-auth-web`)

Provides `useAuth` hook for the web dashboard. Exports `{ useAuth, AuthUser }`. The hook fetches `/api/auth/user` and provides `user`, `isLoading`, `isAuthenticated`, `login()`, `logout()`. The pbx-dashboard resolves this package via a TypeScript path alias (direct source import) since the package exports TypeScript source directly.

### `scripts` (`@workspace/scripts`)

Utility scripts package. Includes `src/seed.ts` which seeds all DB tables with realistic Nigerian ISP call center data.
