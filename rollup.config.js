import ts from 'rollup-plugin-ts'
import copy from 'rollup-plugin-copy'
import { uglify } from 'rollup-plugin-uglify'
import nodeResolve from 'rollup-plugin-node-resolve'
import commonjs from 'rollup-plugin-commonjs';
import json from 'rollup-plugin-json'

export default {
  input: 'src/index.ts',
  output: {
    dir: 'dist'
  },
  
  plugins: [
    ts({
      tsconfig: "tsconfig.json"
    }),
    copy({targets: [
      { src: 'runtime/*', dest: 'dist/runtime' },
      { src: 'assets/*', dest: 'dist/assets' }
    ]}),
    nodeResolve(),
    commonjs(),
    json(),
    uglify()
  ]
}