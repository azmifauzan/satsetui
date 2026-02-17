# Plan: JS Framework Output & Live Preview Workspace

**Tanggal:** 16 Februari 2026  
**Status:** Planning  
**Priority:** High

---

## 📋 Ringkasan

Ekstensi fitur SatsetUI untuk:
1. **JS Framework Output** — Output format yang menghasilkan kode React, Vue, Svelte, dll. yang benar-benar menggunakan framework tersebut (bukan hanya HTML+CSS)
2. **Live Preview Workspace** — Workspace hasil generasi bisa langsung di-preview secara live (running app) di browser, bukan hanya code viewer

---

## 🎯 Tujuan

### Saat Ini (Current State)

- **Output Format** di wizard Step 1 menawarkan: `HTML+CSS`, `React`, `Vue`, `Angular`, `Svelte`, `Custom`
- Tetapi output yang dihasilkan LLM saat ini **hanya HTML+CSS** — opsi React/Vue dll. hanya memberi instruksi ke LLM tanpa framework scaffolding
- Setiap file yang dihasilkan adalah file HTML standalone (single file per page)
- Preview hanya berupa **code viewer** (syntax highlighting) — tidak ada live rendering

### Target (Goal State)

- User memilih framework JS → output yang benar-benar menggunakan framework tersebut:
  - **React**: JSX/TSX components, React Router, proper imports
  - **Vue**: SFC (.vue files), Vue Router, Composition API
  - **Svelte**: .svelte files, SvelteKit routing
  - **Angular**: TypeScript components, Angular Router, modules
  - **HTML+CSS**: Static HTML files (existing behavior)
- **Live Preview Workspace** yang bisa di-run langsung di browser
- User bisa melihat dan berinteraksi dengan template yang sudah digenerate

---

## 🏗️ Arsitektur Teknis

### Phase 1: Enhanced JS Framework Output

#### 1.1 Blueprint Schema Extension

Tambahkan konfigurasi framework-specific ke blueprint:

```json
{
  "outputFormat": "react",
  "frameworkConfig": {
    "language": "typescript",
    "styling": "tailwind",
    "router": true,
    "stateManagement": "zustand",
    "buildTool": "vite"
  }
}
```

Schema update di `app/Blueprints/template-blueprint.schema.json`:

```json
{
  "frameworkConfig": {
    "type": "object",
    "properties": {
      "language": {
        "type": "string",
        "enum": ["javascript", "typescript"],
        "default": "typescript"
      },
      "styling": {
        "type": "string",
        "enum": ["tailwind", "bootstrap", "css-modules", "styled-components"],
        "description": "Inherits from framework choice in Step 1"
      },
      "router": {
        "type": "boolean",
        "default": true
      },
      "stateManagement": {
        "type": "string",
        "enum": ["none", "zustand", "pinia", "redux", "ngrx", "svelte-store"]
      },
      "buildTool": {
        "type": "string",
        "enum": ["vite", "webpack", "turbopack"],
        "default": "vite"
      }
    }
  }
}
```

#### 1.2 Wizard Step 1 Enhancement

Update `Step1FrameworkCategoryOutput.vue`:

- Ketika user memilih output format React/Vue/Svelte/Angular:
  - Tampilkan sub-opsi **Language** (JS / TypeScript)
  - Tampilkan sub-opsi **State Management** (sesuai framework)
  - Tampilkan info bahwa output akan menghasilkan multi-file project
- Auto-map CSS framework ke styling approach yang kompatibel:
  - Tailwind → semua framework bisa
  - Bootstrap → semua framework bisa (via CDN atau package)
  - Pure CSS → CSS modules atau inline styles

```
Output Format: [React] ✓
├── Language: [TypeScript] ✓  [JavaScript]
├── State Management: [Zustand] ✓  [Redux]  [None]
└── Router: [✓ Include routing between pages]
```

#### 1.3 McpPromptBuilder Extension

Update `app/Services/McpPromptBuilder.php` → method `buildForPage()`:

**Perubahan utama:**
- **Output structure** berubah dari single HTML file → multi-file component
- **Framework boilerplate** disertakan sebagai context
- **Import statements** dan **routing config** konsisten antar pages
- **Shared components** (layout, navbar, sidebar) digenerate sebagai file terpisah

**Per-framework prompt template:**

```
[REACT]
Generate a React component using TypeScript + Tailwind CSS.
- Export default function component
- Use React Router v7 for navigation
- Import layout from '../layouts/MainLayout'
- Use Zustand for state if needed
- File: src/pages/{PageName}.tsx

[VUE]
Generate a Vue 3 SFC using <script setup lang="ts"> + Tailwind CSS.
- Use Vue Router for navigation
- Import layout from '@/layouts/MainLayout.vue'
- Use Pinia for state if needed
- File: src/pages/{PageName}.vue

[SVELTE]
Generate a Svelte component using TypeScript + Tailwind CSS.
- Use SvelteKit folder-based routing
- Import layout from '$lib/layouts/MainLayout.svelte'
- Use Svelte stores for state if needed
- File: src/routes/{page-name}/+page.svelte

[ANGULAR]
Generate an Angular component using TypeScript + Tailwind CSS.
- Create component with @Component decorator
- Use Angular Router for navigation
- Use standalone components (Angular 17+ style)
- File: src/app/pages/{page-name}/{page-name}.component.ts
```

#### 1.4 Output Structure per Framework

Setiap generasi menghasilkan **project scaffold** + **per-page components**:

##### React Output Structure
```
generated-template/
├── package.json
├── vite.config.ts
├── tsconfig.json
├── index.html
├── src/
│   ├── main.tsx
│   ├── App.tsx
│   ├── router.tsx
│   ├── layouts/
│   │   └── MainLayout.tsx
│   ├── pages/
│   │   ├── Dashboard.tsx
│   │   ├── Login.tsx
│   │   ├── Settings.tsx
│   │   └── ...
│   ├── components/
│   │   ├── Sidebar.tsx
│   │   ├── Navbar.tsx
│   │   └── ...
│   └── styles/
│       └── globals.css
└── tailwind.config.ts
```

##### Vue Output Structure
```
generated-template/
├── package.json
├── vite.config.ts
├── tsconfig.json
├── index.html
├── src/
│   ├── main.ts
│   ├── App.vue
│   ├── router/index.ts
│   ├── layouts/
│   │   └── MainLayout.vue
│   ├── pages/
│   │   ├── Dashboard.vue
│   │   ├── Login.vue
│   │   ├── Settings.vue
│   │   └── ...
│   ├── components/
│   │   ├── Sidebar.vue
│   │   ├── Navbar.vue
│   │   └── ...
│   └── assets/
│       └── main.css
└── tailwind.config.ts
```

##### Svelte Output Structure
```
generated-template/
├── package.json
├── svelte.config.js
├── vite.config.ts
├── tsconfig.json
├── src/
│   ├── app.html
│   ├── app.css
│   ├── lib/
│   │   ├── layouts/
│   │   │   └── MainLayout.svelte
│   │   └── components/
│   │       ├── Sidebar.svelte
│   │       └── Navbar.svelte
│   └── routes/
│       ├── +layout.svelte
│       ├── +page.svelte (dashboard)
│       ├── login/+page.svelte
│       ├── settings/+page.svelte
│       └── ...
└── tailwind.config.ts
```

#### 1.5 Generation Flow Changes

**Saat ini (single file per page):**
```
Page 1 → LLM → 1 HTML file → store in page_generations.generated_content
Page 2 → LLM → 1 HTML file → store in page_generations.generated_content
```

**Setelah update (multi-file per page):**
```
Step 0 (scaffold)  → Generate boilerplate files (package.json, config, router, layout)
Page 1 (component) → LLM → component file + update router
Page 2 (component) → LLM → component file + update router
...
Step N (finalize)   → Generate final router config, main entry, shared components
```

**Database changes:**

Tambah kolom di `page_generations`:
```sql
ALTER TABLE page_generations ADD COLUMN file_path VARCHAR(255) NULL;
ALTER TABLE page_generations ADD COLUMN file_type VARCHAR(50) DEFAULT 'html';
-- file_type: 'html', 'tsx', 'vue', 'svelte', 'ts', 'config'
```

Tambah table baru `generation_files`:
```sql
CREATE TABLE generation_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    generation_id BIGINT NOT NULL,
    page_generation_id BIGINT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_content LONGTEXT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    is_scaffold BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (generation_id) REFERENCES generations(id) ON DELETE CASCADE,
    FOREIGN KEY (page_generation_id) REFERENCES page_generations(id) ON DELETE SET NULL
);
```

#### 1.6 Scaffold Generator Service

Buat `app/Services/ScaffoldGeneratorService.php`:

```php
class ScaffoldGeneratorService
{
    /**
     * Generate boilerplate/scaffold files for a framework project.
     * These are deterministic (no LLM needed) — pure template files.
     */
    public function generateScaffold(
        string $outputFormat,
        array $frameworkConfig,
        array $pages,
        array $theme
    ): array {
        return match($outputFormat) {
            'react' => $this->generateReactScaffold(...),
            'vue' => $this->generateVueScaffold(...),
            'svelte' => $this->generateSvelteScaffold(...),
            'angular' => $this->generateAngularScaffold(...),
            default => [], // HTML+CSS = no scaffold needed
        };
    }
}
```

Scaffold files (package.json, config, router, layout) **tidak perlu LLM** — dibuat secara deterministik dari template. Hanya page components yang digenerate oleh LLM.

---

### Phase 2: Live Preview Workspace

#### 2.1 Arsitektur Live Preview

```
┌──────────────────────────────────────────────────┐
│  SatsetUI (Laravel + Vue)                        │
│                                                  │
│  Generation/Show.vue                             │
│  ├── Code Viewer (existing)                      │
│  ├── [NEW] Live Preview Panel                    │
│  │   ├── iframe (sandboxed)                      │
│  │   │   └── Preview App (Vite dev server)       │
│  │   ├── Device Switcher (desktop/tablet/mobile) │
│  │   └── Console Output                          │
│  └── Refinement Chat (existing)                  │
│                                                  │
│  Backend:                                        │
│  ├── WorkspaceService.php                        │
│  │   ├── createWorkspace()                       │
│  │   ├── installDependencies()                   │
│  │   ├── startDevServer()                        │
│  │   └── destroyWorkspace()                      │
│  └── PreviewController.php                       │
│      ├── setup() → create workspace + install    │
│      ├── start() → start dev server              │
│      ├── proxy() → proxy preview requests        │
│      └── stop() → cleanup                        │
└──────────────────────────────────────────────────┘
```

#### 2.2 Workspace Management

Setiap generasi yang ingin di-preview akan mendapat **workspace sementara** di server:

```
storage/app/workspaces/
├── gen-123/                    # workspace untuk generation #123
│   ├── package.json
│   ├── vite.config.ts
│   ├── src/
│   │   ├── pages/
│   │   ├── components/
│   │   └── ...
│   ├── node_modules/           # auto-installed
│   └── .satsetui-preview       # metadata file
└── gen-456/
    └── ...
```

**Lifecycle:**
1. User klik "Live Preview" di Generation/Show.vue
2. Backend membuat workspace directory dari generation files
3. Backend menjalankan `npm install` di workspace
4. Backend menjalankan `vite dev` (atau framework-specific dev server)
5. Frontend menampilkan iframe ke dev server URL (via proxy)
6. User bisa navigate antar page di preview
7. Workspace otomatis di-cleanup setelah timeout (30 menit) atau user close

#### 2.3 Backend: WorkspaceService

```php
// app/Services/WorkspaceService.php

class WorkspaceService
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = storage_path('app/workspaces');
    }

    /**
     * Create workspace directory and write all generation files.
     */
    public function createWorkspace(Generation $generation): string
    {
        $workspaceDir = "{$this->basePath}/gen-{$generation->id}";
        // 1. Create directory
        // 2. Write scaffold files (package.json, config, etc.)
        // 3. Write page component files from generation_files table
        // 4. Write shared components (layout, navbar, sidebar)
        return $workspaceDir;
    }

    /**
     * Install npm dependencies in workspace.
     * Runs: npm install --prefer-offline
     */
    public function installDependencies(string $workspaceDir): bool
    {
        $process = Process::path($workspaceDir)
            ->timeout(120)
            ->run('npm install --prefer-offline');
        return $process->successful();
    }

    /**
     * Start Vite dev server for live preview.
     * Returns the port number.
     */
    public function startDevServer(string $workspaceDir): int
    {
        $port = $this->getAvailablePort();
        Process::path($workspaceDir)
            ->background()
            ->run("npx vite --port {$port} --host 0.0.0.0");
        return $port;
    }

    /**
     * Destroy workspace: stop dev server + delete directory.
     */
    public function destroyWorkspace(string $workspaceDir): void
    {
        // 1. Kill dev server process
        // 2. Delete workspace directory
        // 3. Release port
    }
}
```

#### 2.4 Backend: PreviewController

```php
// app/Http/Controllers/PreviewController.php

class PreviewController extends Controller
{
    public function setup(Generation $generation): JsonResponse
    {
        // 1. Create workspace
        // 2. Install dependencies
        // Return: workspace status
    }

    public function start(Generation $generation): JsonResponse
    {
        // 1. Start dev server
        // Return: preview URL
    }

    public function proxy(Request $request, Generation $generation): Response
    {
        // Proxy requests to workspace dev server
        // This avoids CORS issues and keeps dev server internal
    }

    public function stop(Generation $generation): JsonResponse
    {
        // Destroy workspace
    }
}
```

#### 2.5 Frontend: Live Preview Component

```vue
<!-- resources/js/components/generation/LivePreview.vue -->

<script setup lang="ts">
import { ref, onUnmounted } from 'vue'

const props = defineProps<{
  generationId: number
  outputFormat: string
}>()

const previewUrl = ref<string | null>(null)
const isLoading = ref(false)
const deviceMode = ref<'desktop' | 'tablet' | 'mobile'>('desktop')
const consoleOutput = ref<string[]>([])

const deviceWidths = {
  desktop: '100%',
  tablet: '768px',
  mobile: '375px'
}

async function startPreview() {
  isLoading.value = true
  // 1. POST /preview/{id}/setup
  // 2. POST /preview/{id}/start
  // 3. Set previewUrl
  isLoading.value = false
}

async function stopPreview() {
  // POST /preview/{id}/stop
  previewUrl.value = null
}

onUnmounted(() => {
  if (previewUrl.value) stopPreview()
})
</script>

<template>
  <div class="live-preview-panel">
    <!-- Device Switcher -->
    <div class="flex gap-2 mb-4">
      <button @click="deviceMode = 'desktop'">Desktop</button>
      <button @click="deviceMode = 'tablet'">Tablet</button>
      <button @click="deviceMode = 'mobile'">Mobile</button>
    </div>

    <!-- Preview iframe -->
    <div
      class="mx-auto border rounded-lg overflow-hidden"
      :style="{ width: deviceWidths[deviceMode] }"
    >
      <iframe
        v-if="previewUrl"
        :src="previewUrl"
        class="w-full h-[600px]"
        sandbox="allow-scripts allow-same-origin"
      />
      <div v-else class="flex items-center justify-center h-[600px]">
        <button @click="startPreview" :disabled="isLoading">
          {{ isLoading ? 'Setting up...' : 'Start Live Preview' }}
        </button>
      </div>
    </div>
  </div>
</template>
```

#### 2.6 HTML+CSS Preview (Simpler Path)

Untuk output format `HTML+CSS`, live preview **tidak perlu Node.js workspace**:

- Langsung render HTML content di iframe menggunakan `srcdoc`
- CSS framework di-inject via CDN link
- Tidak perlu `npm install` atau dev server
- Instant preview tanpa setup time

```vue
<!-- Simplified preview for HTML+CSS -->
<iframe
  v-if="outputFormat === 'html-css'"
  :srcdoc="generatedHtmlContent"
  class="w-full h-[600px]"
  sandbox="allow-scripts"
/>
```

#### 2.7 WebContainer Alternative (Client-Side)

Sebagai alternatif dari workspace di server, bisa menggunakan **WebContainers** (StackBlitz technology) yang berjalan sepenuhnya di browser:

**Pros:**
- Tidak perlu server-side workspace management
- Tidak perlu install Node.js di server
- Instant startup (~2 detik)
- Lebih aman (sandboxed di browser)
- Tidak ada biaya server tambahan

**Cons:**
- Hanya bekerja di browser modern (Chrome, Edge, Firefox)
- Memory usage tinggi di browser
- Tidak bisa preview Angular (karena berat)
- Dependency install terbatas

```typescript
// Using @webcontainer/api
import { WebContainer } from '@webcontainer/api'

const container = await WebContainer.boot()
await container.mount(generatedFiles)
const installProcess = await container.spawn('npm', ['install'])
await installProcess.exit

const devProcess = await container.spawn('npx', ['vite', '--port', '3000'])
// Get preview URL from container
```

> **Rekomendasi:** Gunakan **WebContainers** untuk Phase 2 awal (lebih sederhana, no server management), lalu migrasi ke server-side workspace jika perlu (untuk Angular atau production scale).

---

### Phase 3: Post-Preview Editing (Future)

Fitur tambahan setelah Phase 1 & 2 berhasil:

- **In-browser code editor** (Monaco Editor) untuk edit file langsung
- **Hot Module Replacement** — edit kode → live preview auto-update
- **Fork & Iterate** — buat variasi dari template yang sudah ada
- **Export to Repository** — push langsung ke GitHub/GitLab
- **Deployment** — 1-click deploy ke Vercel/Netlify

---

## 📊 Database Changes Summary

### New Table: `generation_files`

```sql
CREATE TABLE generation_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    generation_id BIGINT NOT NULL,
    page_generation_id BIGINT NULL,
    file_path VARCHAR(500) NOT NULL,        -- e.g. 'src/pages/Dashboard.tsx'
    file_content LONGTEXT NOT NULL,
    file_type VARCHAR(50) NOT NULL,          -- 'tsx', 'vue', 'svelte', 'ts', 'css', 'json', 'html'
    is_scaffold BOOLEAN DEFAULT FALSE,       -- true for package.json, config files
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (generation_id) REFERENCES generations(id) ON DELETE CASCADE,
    FOREIGN KEY (page_generation_id) REFERENCES page_generations(id) ON DELETE SET NULL
);
```

### New Table: `preview_sessions`

```sql
CREATE TABLE preview_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    generation_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    workspace_path VARCHAR(500) NULL,
    preview_port INT NULL,
    preview_type ENUM('webcontainer', 'server', 'static') NOT NULL,
    status ENUM('creating', 'installing', 'running', 'stopped', 'error') NOT NULL,
    started_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    stopped_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (generation_id) REFERENCES generations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Altered Table: `page_generations`

```sql
ALTER TABLE page_generations ADD COLUMN file_path VARCHAR(255) NULL;
ALTER TABLE page_generations ADD COLUMN file_type VARCHAR(50) DEFAULT 'html';
```

---

## 🔧 New Files Required

### Backend (PHP/Laravel)

| File | Purpose |
|------|---------|
| `app/Services/ScaffoldGeneratorService.php` | Generate deterministic boilerplate files |
| `app/Services/WorkspaceService.php` | Manage preview workspace lifecycle |
| `app/Http/Controllers/PreviewController.php` | Preview setup, start, proxy, stop |
| `app/Models/GenerationFile.php` | Eloquent model for generation_files |
| `app/Models/PreviewSession.php` | Eloquent model for preview_sessions |
| `database/migrations/xxx_create_generation_files.php` | Migration |
| `database/migrations/xxx_create_preview_sessions.php` | Migration |
| `database/migrations/xxx_add_file_columns_to_page_generations.php` | Migration |

### Frontend (Vue/TypeScript)

| File | Purpose |
|------|---------|
| `resources/js/components/generation/LivePreview.vue` | Live preview iframe + controls |
| `resources/js/components/generation/DeviceSwitcher.vue` | Desktop/tablet/mobile mode |
| `resources/js/components/wizard/FrameworkConfigPanel.vue` | JS framework sub-options |
| `resources/js/wizard/frameworkConfig.ts` | Framework config state & validation |
| Update `resources/js/wizard/wizardState.ts` | Add frameworkConfig to state |
| Update `resources/js/wizard/types.ts` | Add FrameworkConfig interface |
| Update `resources/js/wizard/steps/Step1FrameworkCategoryOutput.vue` | Framework sub-options |
| Update `resources/js/pages/Generation/Show.vue` | Add LivePreview panel |

### Routes

```php
// routes/web.php — add inside authenticated+verified group
Route::prefix('preview')->group(function () {
    Route::post('/{generation}/setup', [PreviewController::class, 'setup'])->name('preview.setup');
    Route::post('/{generation}/start', [PreviewController::class, 'start'])->name('preview.start');
    Route::get('/{generation}/proxy/{path?}', [PreviewController::class, 'proxy'])->name('preview.proxy')->where('path', '.*');
    Route::post('/{generation}/stop', [PreviewController::class, 'stop'])->name('preview.stop');
});
```

---

## 📅 Timeline Estimasi

### Phase 1: JS Framework Output (3-4 minggu)

| Minggu | Task |
|--------|------|
| 1 | Blueprint schema extension, wizard UI update, framework config state |
| 2 | ScaffoldGeneratorService (semua framework), generation_files table |
| 2 | McpPromptBuilder update — framework-specific prompt templates |
| 3 | GenerationService update — multi-file output, scaffold generation |
| 3 | Update Generation/Show.vue — file tree for multi-file, ZIP download |
| 4 | Testing: unit + feature tests, end-to-end generation per framework |
| 4 | i18n translations, dark mode support for new UI elements |

### Phase 2: Live Preview (2-3 minggu)

| Minggu | Task |
|--------|------|
| 1 | HTML+CSS static preview (iframe srcdoc — quick win) |
| 1 | WebContainer integration (install @webcontainer/api) |
| 2 | LivePreview.vue component with device switcher |
| 2 | Preview session management (timeout, cleanup) |
| 3 | WorkspaceService (server-side fallback) |
| 3 | Testing, error handling, loading states |

### Phase 3: Polish & Post-Preview (2 minggu, future)

| Minggu | Task |
|--------|------|
| 1 | In-browser code editor (Monaco) |
| 1 | HMR integration for edit-preview loop |
| 2 | Export to GitHub, deploy to Vercel/Netlify |

---

## 💰 Credit Impact

### Framework Output Complexity

Output JS framework lebih kompleks dari HTML+CSS, sehingga memerlukan lebih banyak token:

| Output Format | Estimated Token Multiplier | Credit Adjustment |
|--------------|---------------------------|-------------------|
| HTML+CSS | 1.0x (baseline) | +0 kredit |
| React/Vue | 1.3x | +2 kredit per page |
| Svelte | 1.2x | +1 kredit per page |
| Angular | 1.5x | +3 kredit per page |

> **Note:** Multiplier ini perlu dikalibrasikan dari data actual setelah implementasi. Credit Learning system akan otomatis menyesuaikan setelah cukup data historis.

Update `wizardState.ts`:
```typescript
const FRAMEWORK_CREDIT_MULTIPLIER: Record<string, number> = {
  'html-css': 1.0,
  'react': 1.3,
  'vue': 1.3,
  'svelte': 1.2,
  'angular': 1.5,
}
```

### Preview Cost

- **HTML+CSS preview**: Gratis (client-side only, no server resources)
- **WebContainer preview**: Gratis (client-side only)
- **Server-side preview**: Potentially costs credits/resources (jika diimplementasi)

---

## ⚠️ Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|---------|
| LLM menghasilkan kode framework yang tidak valid | Preview gagal | Scaffold deterministic + LLM hanya generate page content. Validasi via dry compile. |
| WebContainer lamban di browser low-end | UX buruk | Fallback ke static preview (code-only). Deteksi browser capability. |
| Token usage naik signifikan untuk framework output | Kredit habis cepat | Framework credit multiplier + credit learning auto-adjust |
| Multiple concurrent preview sessions | Server overload | Limit 1 active preview per user, auto-cleanup 30 menit |
| npm install gagal di workspace | Preview gagal | Pre-cache common packages, lockfile template, retry mechanism |
| Framework version conflicts | Build error | Pin specific versions di scaffold templates, test regularly |

---

## 🔗 Dependencies Baru

### NPM (devDependencies SatsetUI)

```json
{
  "@webcontainer/api": "^1.x",
  "monaco-editor": "^0.x"   // Phase 3
}
```

### NPM (per-framework scaffold templates)

**React scaffold:**
```json
{
  "react": "^19.x",
  "react-dom": "^19.x",
  "react-router-dom": "^7.x",
  "zustand": "^5.x",
  "@vitejs/plugin-react": "^4.x"
}
```

**Vue scaffold:**
```json
{
  "vue": "^3.x",
  "vue-router": "^4.x",
  "pinia": "^2.x",
  "@vitejs/plugin-vue": "^5.x"
}
```

**Svelte scaffold:**
```json
{
  "svelte": "^5.x",
  "@sveltejs/kit": "^2.x",
  "@sveltejs/vite-plugin-svelte": "^4.x"
}
```

**Angular scaffold:**
```json
{
  "@angular/core": "^19.x",
  "@angular/cli": "^19.x",
  "@angular/router": "^19.x"
}
```

---

## ✅ Definition of Done

### Phase 1 Done When:
- [ ] User bisa pilih React/Vue/Svelte/Angular di Step 1 wizard
- [ ] Blueprint schema mendukung `frameworkConfig`
- [ ] LLM menghasilkan kode framework-specific yang valid
- [ ] Output berupa multi-file project structure (bukan single HTML)
- [ ] Scaffold files (package.json, config) generated deterministically
- [ ] ZIP download berisi full project yang bisa langsung di-run
- [ ] Credit calculation memperhitungkan framework multiplier
- [ ] i18n dan dark mode di semua UI baru
- [ ] Unit + feature tests pass

### Phase 2 Done When:
- [ ] HTML+CSS preview works via iframe srcdoc
- [ ] WebContainer preview works untuk React/Vue/Svelte
- [ ] Device switcher (desktop/tablet/mobile) berfungsi
- [ ] Preview session auto-cleanup setelah 30 menit
- [ ] Loading states dan error handling proper
- [ ] User bisa navigate antar page di preview
- [ ] i18n dan dark mode di preview UI
- [ ] Tests pass

---

## Sat-set! 🚀
