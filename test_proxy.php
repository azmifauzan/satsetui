<?php
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$urls = [
    'root' => 'http://127.0.0.1:3100/generation/6/preview/proxy/',
    'vite_client' => 'http://127.0.0.1:3100/generation/6/preview/proxy/@vite/client',
    'main_ts' => 'http://127.0.0.1:3100/generation/6/preview/proxy/src/main.ts',
    'export_helper' => 'http://127.0.0.1:3100/generation/6/preview/proxy/@id/__x00__plugin-vue:export-helper',
    'vue_dep' => 'http://127.0.0.1:3100/generation/6/preview/proxy/node_modules/.vite/deps/vue.js?v=e191393a',
    'home_vue' => 'http://127.0.0.1:3100/generation/6/preview/proxy/src/pages/Home.vue',
];

foreach ($urls as $name => $url) {
    try {
        $r = Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['Accept' => '*/*', 'Accept-Encoding' => 'identity'])
            ->get($url);
        echo "$name: status={$r->status()}, ct={$r->header('Content-Type')}, len=".strlen($r->body())."\n";
    } catch (Exception $e) {
        echo "$name: ERROR - ".$e->getMessage()."\n";
    }
}
