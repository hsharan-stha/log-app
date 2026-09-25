# ABIS Attendance presentation (EN / 日本語)

## PDF

Open:

`docs/attendance-presentation/ABIS-Attendance-Features-EN-JA.pdf`

## Regenerate screenshots

Requires the app running at `http://localhost:8000` and demo users seeded.

```bash
cd docs/attendance-presentation
npm install puppeteer-core@23
node capture-screens.mjs
```

## Regenerate PDF

```bash
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  --headless=new --disable-gpu --no-pdf-header-footer \
  --print-to-pdf="$(pwd)/ABIS-Attendance-Features-EN-JA.pdf" \
  "file://$(pwd)/slides.html"
```
