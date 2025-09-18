import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'path'

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'), // now @ points to resources/js
        },
    },
    server: {
        host: '0.0.0.0',          // bind inside container
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',      // tell the browser to connect to IPv4 on the host
            clientPort: 5173,       // matches the published port
            protocol: 'ws'
        },
        cors: true,
        watch: { usePolling: true } // good for Docker on macOS/Windows
    },
    plugins: [vue(), tailwindcss()],
})
