import typescript from '@rollup/plugin-typescript';
import copy from 'rollup-plugin-copy';
import { terser } from 'rollup-plugin-terser';

export default {
  input: 'src/index.ts',
  output: {
    file: 'dist/bundle.js',
    format: 'cjs'
  },
  plugins: [
    typescript(),
    copy({
      targets: [
        { src: 'runtime/*', dest: 'dist/runtime' },
        { src: 'assets/*', dest: 'dist/assets' }
      ]
    }),
    // terser()
  ]
};
