import { createServer, request as httpRequest } from 'node:http';
import { createReadStream, existsSync } from 'node:fs';
import { extname, join, normalize } from 'node:path';

const host = process.env.THRIVEWELL_FRONTEND_HOST || '0.0.0.0';
const port = Number(process.env.THRIVEWELL_FRONTEND_PORT || 3000);
const backend = new URL(process.env.THRIVEWELL_BACKEND_URL || 'http://127.0.0.1:8000');
const publicRoot = join(process.cwd(), 'public');

const mimeTypes = {
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.ico': 'image/x-icon',
};

function serveStatic(req, res) {
  const url = new URL(req.url || '/', `http://${req.headers.host || `${host}:${port}`}`);
  if (!url.pathname.startsWith('/build/') && !url.pathname.startsWith('/assets/')) return false;

  const requested = normalize(decodeURIComponent(url.pathname)).replace(/^[/\\]+/, '');
  const filePath = join(publicRoot, requested);
  if (!filePath.startsWith(publicRoot) || !existsSync(filePath)) return false;

  res.writeHead(200, { 'content-type': mimeTypes[extname(filePath)] || 'application/octet-stream' });
  createReadStream(filePath).pipe(res);
  return true;
}

function unavailable(res) {
  res.writeHead(502, { 'content-type': 'text/html; charset=utf-8' });
  res.end(`<!doctype html><html lang="en"><meta charset="utf-8"><title>ThriveWell dev server</title><body style="font-family:system-ui;padding:40px;background:#101827;color:#f8fafc"><h1>Laravel backend is not running yet.</h1><p>Start it in another terminal with <code>php artisan serve</code>, then refresh this page.</p><p>Frontend dev server: <code>http://${host}:${port}</code><br>Backend target: <code>${backend.origin}</code></p></body></html>`);
}

function proxy(req, res) {
  const target = new URL(req.url || '/', backend);
  const headers = { ...req.headers, host: backend.host, 'x-forwarded-host': req.headers.host || `${host}:${port}` };
  const upstream = httpRequest({
    protocol: target.protocol,
    hostname: target.hostname,
    port: target.port || 80,
    path: `${target.pathname}${target.search}`,
    method: req.method,
    headers,
  }, (upstreamRes) => {
    res.writeHead(upstreamRes.statusCode || 200, upstreamRes.headers);
    upstreamRes.pipe(res);
  });
  upstream.on('error', () => unavailable(res));
  req.pipe(upstream);
}

createServer((req, res) => {
  if (!serveStatic(req, res)) proxy(req, res);
}).listen(port, host, () => {
  console.log(`ThriveWell frontend dev server listening on http://${host}:${port}`);
  console.log(`Open http://127.0.0.1:${port} locally, or use your forwarded/container port URL.`);
  console.log(`Proxying application requests to ${backend.origin}`);
  console.log('Start the backend with: php artisan serve');
});
