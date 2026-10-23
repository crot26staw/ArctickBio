import { defineConfig } from 'vite';
import { fileURLToPath } from 'node:url';
import { resolve } from 'node:path';
import htmlInclude from './vite/html-include.js';

const root = fileURLToPath(new URL('./src', import.meta.url));

// Каждая новая страница добавляется сюда
const pages = {
  index: 'index.html',
  about: 'about.html',
  catalog: 'catalog.html',
  product: 'product.html',
};

export default defineConfig({
  root,
  base: './',
  publicDir: resolve(root, '../public'),
  plugins: [htmlInclude({ root })],
  server: {
    open: true,
  },
  build: {
    outDir: resolve(root, '../dist'),
    emptyOutDir: true,
    assetsInlineLimit: 0,
    rollupOptions: {
      input: Object.fromEntries(
        Object.entries(pages).map(([name, file]) => [name, resolve(root, file)]),
      ),
    },
  },
});
