import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public/react',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        ClientProgramView: resolve(__dirname, 'src/ClientProgramView/index.jsx'),
        ClientProgramWizard: resolve(__dirname, 'src/ClientProgramWizard/index.jsx'),
        MasterProgramTreeView: resolve(__dirname, 'src/MasterProgramTreeView/index.jsx'),
      },
      output: {
        entryFileNames: '[name]/[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: ({ name }) => {
          if (name.includes('style') || name.endsWith('.css')) return 'assets/[name][extname]';
          return 'assets/[name]-[hash][extname]';
        },
      },
     // external: ['sweetalert2'], // Explicitly mark `sweetalert2` as external
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  optimizeDeps: {
    include: ['sweetalert2'], // Pre-bundle `sweetalert2` for faster loading
  },
});
