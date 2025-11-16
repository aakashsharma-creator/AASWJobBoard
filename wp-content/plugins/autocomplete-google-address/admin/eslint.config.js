module.exports = {
  parser: '@typescript-eslint/parser',
  plugins: ['@typescript-eslint', 'unused-imports', 'react'],
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:react/recommended',
    'plugin:react/jsx-runtime',
    'plugin:prettier/recommended', // Ensures Prettier and ESLint compatibility
  ],
  rules: {
    'unused-imports/no-unused-imports': 'error', // Automatically removes unused imports
    'unused-imports/no-unused-vars': [
      'warn',
      {
        vars: 'all',
        varsIgnorePattern: '^_',
        args: 'after-used',
        argsIgnorePattern: '^_',
      },
    ],
    'react/react-in-jsx-scope': 'off', // For React 17+
    'prettier/prettier': 'error', // Ensures Prettier issues are shown as errors
  },
};

// import js from "@eslint/js";
// import globals from "globals";
// import reactHooks from "eslint-plugin-react-hooks";
// import reactRefresh from "eslint-plugin-react-refresh";
// import tseslint from "typescript-eslint";

// export default tseslint.config(
//   { ignores: ["dist"] },
//   {
//     extends: [js.configs.recommended, ...tseslint.configs.recommended],
//     files: ["**/*.{ts,tsx}"],
//     languageOptions: {
//       ecmaVersion: 2020,
//       globals: globals.browser,
//     },
//     plugins: {
//       "react-hooks": reactHooks,
//       "react-refresh": reactRefresh,
//     },
//     rules: {
//       ...reactHooks.configs.recommended.rules,
//       "react-refresh/only-export-components": [
//         "warn",
//         { allowConstantExport: true },
//       ],
//       "unused-imports/no-unused-imports": "warn",
//       "unused-imports/no-unused-vars": [
//         "warn",
//         {
//           vars: "all",
//           varsIgnorePattern: "^_",
//           args: "after-used",
//           argsIgnorePattern: "^_",
//         },
//       ],
//     },
//   }
// );
