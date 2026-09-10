import { test, expect, type Page } from '@playwright/test';
import { compose } from './setup.ts';

const title = 'RustによるWebサーバーのパフォーマンス最適化';
const keyword = 'Development Backend News Article 01';

async function searchSeededArticle(page: Page) {
  await page.getByRole('searchbox', { name: '記事をキーワードで検索' }).fill(keyword);
  await page.getByRole('button', { name: '検索', exact: true }).click();
  await expect(page).toHaveURL((url) => url.pathname === '/articles' && url.searchParams.get('keyword') === keyword);
  await expect(page.getByRole('article')).toHaveCount(1);
  await expect(page.getByRole('article').getByRole('link', { name: title, exact: true })).toBeVisible();
}

test.beforeEach(async ({ page }) => {
  compose(['exec', '-T', 'backend', 'php', 'artisan', 'db:seed', '--class=DevelopmentSeeder', '--no-interaction']);
  // Only the local SPA and real local API are allowed; never mock authentication or article responses.
  await page.route('**/*', (route) => {
    const origin = new URL(route.request().url()).origin;
    return ['http://localhost:5173', 'http://localhost:8000'].includes(origin)
      ? route.continue()
      : route.abort();
  });
  await page.goto('/login');
  await page.getByLabel('メールアドレス', { exact: true }).fill(process.env.E2E_EMAIL!);
  await page.getByLabel('パスワード', { exact: true }).fill(process.env.E2E_PASSWORD!);
  await page.getByRole('button', { name: 'ログイン', exact: true }).click();
  await expect(page).toHaveURL('http://localhost:5173/articles');
  await expect(page.getByRole('heading', { name: '記事一覧', exact: true })).toBeVisible();
  await expect(page.getByRole('article').first()).toBeVisible();
});

test('ログイン・記事一覧・ログアウト', async ({ page }) => {
  await page.getByRole('button', { name: 'ログアウト', exact: true }).click();
  await expect(page).toHaveURL('http://localhost:5173/login');
  await page.goto('/articles');
  await expect(page).toHaveURL('http://localhost:5173/login');
  await expect(page.getByRole('heading', { name: 'ログイン', exact: true })).toBeVisible();
});

test('キーワードで記事を絞り込める', async ({ page }) => {
  await searchSeededArticle(page);
});

test('お気に入りを登録・解除でき、再読み込み後も保存される', async ({ page }) => {
  await searchSeededArticle(page);
  await page.getByRole('link', { name: title, exact: true }).click();
  await expect(page).toHaveURL(/\/articles\/\d+$/);
  const add = page.getByRole('button', { name: 'お気に入り', exact: true });
  const remove = page.getByRole('button', { name: 'お気に入り解除', exact: true });
  await expect(add).toHaveAttribute('aria-pressed', 'false');
  await add.click();
  await expect(remove).toHaveAttribute('aria-pressed', 'true');
  await page.reload();
  await expect(remove).toHaveAttribute('aria-pressed', 'true');
  await remove.click();
  await expect(add).toHaveAttribute('aria-pressed', 'false');
  await page.reload();
  await expect(add).toHaveAttribute('aria-pressed', 'false');
});
