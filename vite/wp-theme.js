import fs from 'node:fs';
import path from 'node:path';

/**
 * Режим сборки под тему WordPress (vite --mode wp, см. WORDPRESS.md → «Сборка ассетов»):
 * - копирует картинки, иконки и видео из src/assets в тему (assets/) — их выводит PHP через arctik_asset();
 *   копируются только новые и изменённые файлы;
 * - на время dev-сервера кладёт в тему файл hot с адресом сервера: по нему inc/vite.php подключает
 *   исходники с dev-сервера вместо сборки.
 */
const COPY_DIRS = ['images', 'icons', 'video'];

function syncDir(from, to) {
  if (!fs.existsSync(from)) return;
  fs.mkdirSync(to, { recursive: true });

  for (const entry of fs.readdirSync(from, { withFileTypes: true })) {
    const src = path.join(from, entry.name);
    const dest = path.join(to, entry.name);

    if (entry.isDirectory()) {
      syncDir(src, dest);
      continue;
    }

    const srcStat = fs.statSync(src);
    const destStat = fs.existsSync(dest) ? fs.statSync(dest) : null;
    if (destStat && destStat.size === srcStat.size && destStat.mtimeMs >= srcStat.mtimeMs) continue;

    fs.copyFileSync(src, dest);
  }
}

export default function wpTheme({ root, themeDir }) {
  const hotFile = path.join(themeDir, 'hot');

  const copyAssets = () => {
    for (const dir of COPY_DIRS) {
      syncDir(path.join(root, 'assets', dir), path.join(themeDir, 'assets', dir));
    }
  };

  const removeHotFile = () => fs.rmSync(hotFile, { force: true });

  return {
    name: 'wp-theme',

    buildStart() {
      copyAssets();
    },

    configureServer(server) {
      server.httpServer?.once('listening', () => {
        fs.writeFileSync(hotFile, server.config.server.origin);
      });

      server.httpServer?.once('close', removeHotFile);
      for (const signal of ['SIGINT', 'SIGTERM', 'SIGHUP']) {
        process.once(signal, () => {
          removeHotFile();
          process.exit();
        });
      }
    },
  };
}
