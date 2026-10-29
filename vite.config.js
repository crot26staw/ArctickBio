import { defineConfig } from 'vite';
import { fileURLToPath } from 'node:url';
import { resolve } from 'node:path';
import htmlInclude from './vite/html-include.js';
import wpTheme from './vite/wp-theme.js';

const root = fileURLToPath(new URL('./src', import.meta.url));

// Каждая новая страница добавляется сюда
const pages = {
  index: 'index.html',
  about: 'about.html',
  catalog: 'catalog.html',
  product: 'product.html',
  buyers: 'buyers.html',
  contacts: 'contacts.html',
  documents: 'documents.html',
  oferta: 'oferta.html',
  privacy: 'privacy.html',
  cookie: 'cookie.html',
  agreement: 'agreement.html',
  'personal-data': 'personal-data.html',
  marketing: 'marketing.html',
  checkout: 'checkout.html',
  'order-received': 'order-received.html',
  order: 'order.html',
  account: 'account.html',
  'account-orders': 'account-orders.html',
  'account-orders-empty': 'account-orders-empty.html',
  'account-addresses': 'account-addresses.html',
};

// Тема WordPress: vite --mode wp (сборка в тему по manifest.json, см. WORDPRESS.md)
const themeDir = fileURLToPath(new URL('./wp-theme/arctik-bio', import.meta.url));
const devOrigin = 'http://localhost:5180';

const wpConfig = {
  root,
  base: './',
  publicDir: false,
  plugins: [wpTheme({ root, themeDir })],
  server: {
    port: 5180,
    strictPort: true,
    origin: devOrigin,
    cors: true,
  },
  build: {
    outDir: resolve(themeDir, 'dist'),
    emptyOutDir: true,
    assetsInlineLimit: 0,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(root, 'js/main.js'),
        style: resolve(root, 'scss/main.scss'),
      },
    },
  },
};

export default defineConfig(({ mode }) => (mode === 'wp' ? wpConfig : {
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
}));
