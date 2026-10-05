import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'

export default defineConfig({
	build: {
		outDir: 'js',
		emptyOutDir: true,
		rollupOptions: {
			input: {
				'zenodo-main': fileURLToPath(new URL('./src/main.js', import.meta.url)),
				'zenodo-admin': fileURLToPath(new URL('./src/admin.js', import.meta.url)),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: '[name]-[hash].js',
				assetFileNames: '[name]-[hash].[ext]',
			},
		},
	},
})
