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

    base: process.env.command === 'build' ? '/public/' : '/',

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

  // server: {
  //   host: false,
  //   port: 5173,
  //   origin: "http://a25sewt301.studev.groept.be/public",
  //   base: "/public/",
  //   strictPort: true,
  //   //allowedHosts: ['https://a25sewt301.studev.groept.be/'],
  //   proxy: {
  //     '/api': {
  //       target: "/src",
  //       changeOrigin: true,
  //       //rewrite: (path) => path.replace(/^\/api/, '')
  //     }
  //   }
  // },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    emptyOutDir: true,
    sourcemap: false,
    manifest: false,
  },
})
