import path from "path";
import { defineConfig } from "vite";

export default defineConfig({
  build: {
    outDir: path.resolve(__dirname, "dist"), // Output directory
    rollupOptions: {
      input: "/src/index.ts", // Entry file
      output: {
        format: "iife", // Immediately Invoked Function Expression for browser compatibility
        entryFileNames: "index.js", // Output filename
      },
    },
  },
});
