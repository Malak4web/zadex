import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import {fileURLToPath} from 'node:url';
export default defineConfig({ plugins: [vue()], server: { host:'127.0.0.1', port:5186, strictPort:true },build:{rollupOptions:{input:{main:fileURLToPath(new URL('./index.html',import.meta.url)),docs:fileURLToPath(new URL('./docs/index.html',import.meta.url))},output:{manualChunks(id){if(id.endsWith('/src/docs/reference.json'))return 'docs-reference';if(id.endsWith('/src/docs/guides.js'))return 'docs-guides';}}}} });
