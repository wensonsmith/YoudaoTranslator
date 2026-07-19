/**
 * Download matching txiki.js binaries for both architectures from GitHub Releases.
 * Runs as a postinstall / prebuild step so users never need to fetch files manually.
 */

const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");
const https = require("https");
const { createWriteStream, mkdirSync } = require("fs");

const TXIKI_VERSION = "v26.6.0";
const RUNTIME_DIR = path.join(__dirname, "..", "runtime");

const ARCHS = [
  { name: "txiki-arm64",    asset: "txiki-macos-arm64.zip" },
  { name: "txiki-x86_64",   asset: "txiki-macos-x86_64.zip" },
];

function download(url, dest) {
  return new Promise((resolve, reject) => {
    const file = createWriteStream(dest);
    https
      .get(url, { headers: { "User-Agent": "YoudaoTranslator/3.x" } }, (res) => {
        // Follow redirects
        if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
          file.close();
          fs.unlinkSync(dest);
          return download(res.headers.location, dest).then(resolve).catch(reject);
        }
        if (res.statusCode !== 200) {
          file.close();
          fs.unlinkSync(dest);
          return reject(new Error(`HTTP ${res.statusCode}`));
        }
        res.pipe(file);
        file.on("finish", () => { file.close(); resolve(); });
      })
      .on("error", (err) => {
        file.close();
        try { fs.unlinkSync(dest); } catch {}
        reject(err);
      });
  });
}

async function main() {
  mkdirSync(RUNTIME_DIR, { recursive: true });

  for (const { name, asset } of ARCHS) {
    const binPath = path.join(RUNTIME_DIR, name);

    // Skip if already downloaded
    if (fs.existsSync(binPath)) {
      console.log(`[txiki] ${name} already exists, skipping`);
      continue;
    }

    const url = `https://github.com/saghul/txiki.js/releases/download/${TXIKI_VERSION}/${asset}`;
    const zipPath = path.join(RUNTIME_DIR, `${asset}`);

    console.log(`[txiki] Downloading ${asset} ...`);
    await download(url, zipPath);

    // Extract the tjs binary from the zip
    console.log(`[txiki] Extracting ${asset} ...`);
    execSync(`unzip -o "${zipPath}" -d "${RUNTIME_DIR}/${name}-extracted"`, { stdio: "pipe" });

    // Find the tjs binary
    const extracted = path.join(RUNTIME_DIR, `${name}-extracted`);
    const files = fs.readdirSync(extracted);
    const innerDir = files.find((f) => fs.statSync(path.join(extracted, f)).isDirectory());
    const tjsPath = path.join(extracted, innerDir || "", "tjs");

    if (!fs.existsSync(tjsPath)) {
      console.error(`[txiki] Could not find tjs binary in ${asset}`);
      process.exit(1);
    }

    fs.renameSync(tjsPath, binPath);
    fs.chmodSync(binPath, 0o755);

    // Cleanup
    fs.rmSync(extracted, { recursive: true, force: true });
    fs.unlinkSync(zipPath);

    console.log(`[txiki] ${name} ready`);
  }

  console.log("[txiki] All binaries ready");
}

main().catch((err) => {
  console.error("[txiki] Download failed:", err.message);
  console.error("[txiki] Run `npm run download-txiki` to retry, or manually place binaries in runtime/");
  process.exit(1);
});
