import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  publicDir: false, // 禁用默认的public目录处理
  build: {
    outDir: 'public/static/dist',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'public/static/js/main.js'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]'
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'public/static')
    }
  },
  server: {
    port: 3000,
    open: false
  }
})