import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

// When running inside Docker dev container, bind Vite to all interfaces
// and tell the browser to connect HMR to localhost:5173.
const isDocker = process.env.RUNNING_IN_DOCKER === 'true';

export default defineConfig({
    server: isDocker
        ? {
              host: '0.0.0.0',
              port: 5173,
              hmr: {
                  host: 'localhost',
                  port: 5173,
              },
          }
        : {},
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
});
