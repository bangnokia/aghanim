import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'resources/dist',
    lib: {
      entry: path.resolve(__dirname, 'resources/js/utils/aghanim.ts'),
      name: 'Aghanim',
      fileName: 'aghanim',
    },
    rollupOptions: {
      external: ['react', 'react-dom', '@inertiajs/react'],
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
          '@inertiajs/react': 'Inertia',
        },
      },
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
});