import { defineConfig } from 'vite'
import react, { reactCompilerPreset } from '@vitejs/plugin-react'
import babel from '@rolldown/plugin-babel'
import tailwindcss from '@tailwindcss/vite'

const usePolling = process.env.VITE_USE_POLLING === 'true'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    tailwindcss(),
    react(),
    babel({ presets: [reactCompilerPreset()] })
  ],
  resolve: {
    tsconfigPaths: true
  },
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    watch: {
      usePolling,
      interval: usePolling ? 300 : undefinded,
    },
    proxy: {
      '/api': {
        target: 'http://php:8000',
        changeOrigin: true,
        //rewrite: (path) => path.replace(/^\/api/, '')
      },
      '/uploads': {
        target: 'http://php:8000',
        changeOrigin: true,
      }
    }
  }
})
