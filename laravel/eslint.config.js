import js from '@eslint/js';
import { defineConfig } from 'eslint/config';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';

export default defineConfig([
    {
        ignores: [
            'bootstrap/cache/**',
            'node_modules/**',
            'public/build/**',
            'public/hot',
            'storage/framework/**',
            'vendor/**',
        ],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/essential'],
    {
        files: ['resources/**/*.{js,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                route: 'readonly',
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'off',
        },
    },
    {
        files: ['*.config.js'],
        languageOptions: {
            globals: globals.node,
        },
    },
]);
