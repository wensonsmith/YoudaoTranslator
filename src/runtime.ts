/**
 * Runtime detection shim.
 *
 * On txiki.js (bundled with the workflow), globals `tjs.getenv` and `tjs.args`
 * are used. On Node.js (fallback for arm64 Macs without Rosetta 2),
 * `process.env` and `process.argv` take over.
 */

declare var tjs: any;

export function getEnv(key: string): string {
  if (typeof tjs !== "undefined" && tjs?.getenv) {
    return tjs.getenv(key);
  }
  return process.env[key] || "";
}

export function getArgs(): string[] {
  if (typeof tjs !== "undefined" && tjs?.args) {
    return Array.from(tjs.args);
  }
  return process.argv.slice(2);
}
