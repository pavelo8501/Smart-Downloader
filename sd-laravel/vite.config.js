import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue"

export default defineConfig({
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        laravel({
            input: ['resources/ts/app.ts'],
            refresh: true,
        }),
    ],
    // resolve: {
    //     alias: {
    //         '@': '/resources/ts',
    //     },
    // },
});
