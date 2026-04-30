import { defineConfig } from 'vite'
import react, { reactCompilerPreset } from '@vitejs/plugin-react'
import babel from '@rolldown/plugin-babel'
import tailwindcss from '@tailwindcss/vite'


// https://vite.dev/config/
export default defineConfig({
    plugins: [
        tailwindcss(),
        react(),
        babel({ presets: [reactCompilerPreset()] })
    ],

    base: '/public/',

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,

        proxy: {'/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                //rewrite: (path) => path.replace(/^\/api/, '')
            },
        },
    },

    resolve: {
        tsconfigPaths: true
    },
    build: {
        outDir: '../backend/public',
        assetsDir: 'assets',
        emptyOutDir: false,
        sourcemap: false,
        manifest: false,
    },
})
