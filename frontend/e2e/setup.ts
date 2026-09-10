import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

export function compose(args: string[]) {
  return execFileSync('docker', ['compose', '-f', 'compose.yml', ...args], {
    cwd: fileURLToPath(new URL('../../', import.meta.url)),
    encoding: 'utf8',
    timeout: 30_000,
  });
}

export default function setup() {
  if (!process.env.E2E_EMAIL || !process.env.E2E_PASSWORD) {
    throw new Error('Set E2E_EMAIL and E2E_PASSWORD in frontend/.env.e2e (DevelopmentSeeder user).');
  }
  const running = compose(['ps', '--services', '--status', 'running']).split('\n');
  if (running.includes('scheduler') || running.includes('queue-worker')) {
    throw new Error('Run docker compose stop scheduler queue-worker before E2E tests.');
  }
}
