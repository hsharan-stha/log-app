import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = 'http://localhost:8000';
const OUT = path.join(__dirname, 'screenshots');

async function login(page, email, password) {
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  await page.click('#email', { clickCount: 3 });
  await page.type('#email', email);
  await page.click('#password', { clickCount: 3 });
  await page.type('#password', password);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('button[type="submit"]'),
  ]);
}

async function shot(page, name) {
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: false });
  console.log('saved', file);
}

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: 'new',
  defaultViewport: { width: 1440, height: 900 },
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

try {
  fs.mkdirSync(OUT, { recursive: true });
  const page = await browser.newPage();

  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  await shot(page, '01-login');

  await login(page, 'admin@example.com', 'password');
  await page.goto(`${BASE}/admin/attendance`, { waitUntil: 'networkidle2' });
  await shot(page, '02-admin-calendar');

  await page.goto(`${BASE}/admin/attendance/day/2026-09-23`, { waitUntil: 'networkidle2' });
  await shot(page, '03-admin-day');

  await page.goto(`${BASE}/admin/devices`, { waitUntil: 'networkidle2' });
  await shot(page, '04-admin-devices');

  const client = await page.createCDPSession();
  await client.send('Network.clearBrowserCookies');

  await login(page, 'guardian@example.com', 'password');
  await page.goto(`${BASE}/guardian`, { waitUntil: 'networkidle2' });
  await shot(page, '05-guardian-dashboard');

  await page.goto(`${BASE}/guardian/attendance`, { waitUntil: 'networkidle2' });
  await shot(page, '06-guardian-calendar');

  await client.send('Network.clearBrowserCookies');
  await login(page, 'attendance@example.com', 'password');
  await page.goto(`${BASE}/attendance`, { waitUntil: 'networkidle2' });
  await shot(page, '07-operator-home');

  await page.goto(`${BASE}/attendance/setup`, { waitUntil: 'networkidle2' });
  await shot(page, '08-kiosk-setup');
} finally {
  await browser.close();
}
