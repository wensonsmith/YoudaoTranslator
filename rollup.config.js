import ts from 'rollup-plugin-ts'
import copy from 'rollup-plugin-copy'
import { uglify } from 'rollup-plugin-uglify'

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
    uglify()
  ]
}