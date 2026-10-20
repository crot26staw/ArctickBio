import fs from 'node:fs';
import path from 'node:path';

/**
 * Подключение partials в HTML:
 *   <include src="partials/header.html"></include>
 *   <include src="partials/product-card.html" data='{"title": "Коллаген"}'></include>
 *
 * Путь src — от корня src/. Внутри partial переменные пишутся как {{ title }}.
 * Незаполненные переменные заменяются пустой строкой. Partials могут подключать другие partials.
 */
const INCLUDE_RE = /<include\s+src="([^"]+)"(?:\s+data='([^']*)')?\s*(?:\/>|><\/include>)/g;
const VAR_RE = /\{\{\s*([\w-]+)\s*\}\}/g;
const MAX_DEPTH = 10;

function processIncludes(html, root, depth = 0) {
  if (depth > MAX_DEPTH) {
    throw new Error('[html-include] слишком глубокая вложенность partials');
  }

  return html.replace(INCLUDE_RE, (_, src, json) => {
    const file = path.resolve(root, src);
    if (!fs.existsSync(file)) {
      throw new Error(`[html-include] файл не найден: ${src}`);
    }

    const data = json ? JSON.parse(json) : {};
    const partial = fs
      .readFileSync(file, 'utf8')
      .replace(VAR_RE, (__, key) => (key in data ? String(data[key]) : ''));

    return processIncludes(partial, root, depth + 1);
  });
}

export default function htmlInclude({ root }) {
  const partialsDir = path.resolve(root, 'partials');

  return {
    name: 'html-include',
    transformIndexHtml: {
      order: 'pre',
      handler: (html) => processIncludes(html, root),
    },
    configureServer(server) {
      server.watcher.add(partialsDir);
      server.watcher.on('change', (file) => {
        if (file.startsWith(partialsDir)) {
          server.ws.send({ type: 'full-reload' });
        }
      });
    },
  };
}
