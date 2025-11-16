import react from '@vitejs/plugin-react';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../assets',
    emptyOutDir: true,
    rollupOptions: {
      input: './index.html',
      external: ['react', 'react-dom'], // Exclude React and ReactDOM
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
        },
        entryFileNames: 'admin.js',
        assetFileNames: 'admin.css',
        format: 'iife',
      },
    },
  },
});
