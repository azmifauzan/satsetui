<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

$workspaceDir = storage_path('app/workspaces/gen-12');

echo "Migrating workspace: {$workspaceDir}\n";

// 1. Update package.json
$packageJsonPath = $workspaceDir . '/package.json';
$packageJson = json_decode(File::get($packageJsonPath), true);

unset($packageJson['devDependencies']['@tailwindcss/vite']);
$packageJson['devDependencies']['tailwindcss'] = '^3.4.0';
$packageJson['devDependencies']['postcss'] = '^8.4.0';
$packageJson['devDependencies']['autoprefixer'] = '^10.4.0';

File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Updated package.json\n";

// 2. Patch vite.config.ts
$vitePath = $workspaceDir . '/vite.config.ts';
if (File::exists($vitePath)) {
    $viteContent = File::get($vitePath);
    $viteContent = preg_replace("/^import tailwindcss from '@tailwindcss\/vite'\n?/m", '', $viteContent);
    $viteContent = preg_replace('/,?\s*tailwindcss\(\)\s*,?/', '', $viteContent);
    $viteContent = preg_replace('/\[\s*,/', '[', $viteContent);
    $viteContent = preg_replace('/,\s*\]/', ']', $viteContent);
    File::put($vitePath, $viteContent);
    echo "Patched vite.config.ts\n";
}

// 3. Patch main CSS
$cssPath = $workspaceDir . '/src/assets/main.css';
if (File::exists($cssPath)) {
    $cssContent = File::get($cssPath);
    $cssContent = str_replace('@import "tailwindcss";', "@tailwind base;\n@tailwind components;\n@tailwind utilities;", $cssContent);
    File::put($cssPath, $cssContent);
    echo "Patched src/assets/main.css\n";
}

// 4. Create tailwind.config.js
$tailwindConfigPath = $workspaceDir . '/tailwind.config.js';
File::put($tailwindConfigPath, "/** @type {import('tailwindcss').Config} */\nexport default {\n  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx,vue,svelte}'],\n  darkMode: 'class',\n  theme: { extend: {} },\n  plugins: [],\n}\n");
echo "Created tailwind.config.js\n";

// 5. Create postcss.config.js
$postcssConfigPath = $workspaceDir . '/postcss.config.js';
File::put($postcssConfigPath, "export default {\n  plugins: {\n    tailwindcss: {},\n    autoprefixer: {},\n  },\n}\n");
echo "Created postcss.config.js\n";

// 6. Fix router child paths
$routerPath = $workspaceDir . '/src/router/index.ts';
if (File::exists($routerPath)) {
    $routerContent = File::get($routerPath);
    $routerContent = preg_replace("/path:\s*'\/([^']+)'/", "path: '\$1'", $routerContent);
    File::put($routerPath, $routerContent);
    echo "Fixed router child paths\n";
}

// 7. Delete node_modules
$nodeModulesPath = $workspaceDir . '/node_modules';
if (File::isDirectory($nodeModulesPath)) {
    File::deleteDirectory($nodeModulesPath);
    echo "Deleted node_modules\n";
}

echo "Migration complete!\n";
