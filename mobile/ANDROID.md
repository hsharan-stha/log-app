# Android app — Staff attendance (WebView)

This document describes how to build and run the **Android** wrapper for the Laravel attendance kiosk. The app is a **Capacitor** shell that loads your `/attendance` page in a full-screen **WebView**.

**Project root (this repo):** `logApp`  
**Capacitor / Android project:** `mobile/capacitor/` and `mobile/capacitor/android/`

---

## 1. What you need installed

| Tool | Purpose |
|------|---------|
| **Android Studio** | SDK, emulator, Gradle, and the IDE used to run and build the app. [Download](https://developer.android.com/studio) |
| **Node.js 18+** (LTS) | `npm install` and `npx cap sync` in `mobile/capacitor` |
| **Your Laravel app** | Running and reachable from the tablet or emulator (see §3) |

Optional but useful:

- A physical **Android tablet or phone** with USB debugging enabled for direct install from Android Studio.

---

## 2. How the Android app loads your site

Capacitor reads **`mobile/capacitor/capacitor.config.ts`**. The `server` block points the WebView at a URL:

```ts
server: {
  url: 'http://10.0.2.2:8000/attendance', // change this
  cleartext: true,                       // required for http:// in dev
},
```

- **`cleartext: true`** allows **HTTP** (not HTTPS). Needed for local development. For Play Store builds you should use **HTTPS** and then tighten security (see §10).

Whenever you change `capacitor.config.ts`, run **`npm run sync`** from `mobile/capacitor` so the copy embedded in the Android project updates.

---

## 3. Laravel: make the server visible on the network

On a **real device**, `http://localhost:8000` on the tablet is **not** your computer.

1. Start Laravel bound to all interfaces:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. On your computer, find the LAN IP, e.g. macOS:

   ```bash
   ipconfig getifaddr en0
   ```

   (Wi‑Fi is often `en0`; sometimes `en1`.)

3. In **`mobile/capacitor/capacitor.config.ts`**, set:

   ```ts
   server: {
     url: 'http://YOUR_LAN_IP:8000/attendance',
     cleartext: true,
   },
   ```

4. **Firewall:** allow incoming TCP **8000** on the machine running Laravel (or turn the firewall off briefly to test).

### Android emulator special case

The emulator’s `localhost` is inside the VM. To reach **your host machine’s** `localhost:8000`, use:

```text
http://10.0.2.2:8000/attendance
```

That is the default in this repo’s `capacitor.config.ts` so **emulator + `php artisan serve` on the host** works without editing the IP.

---

## 4. First-time setup (from repo root)

```bash
cd mobile/capacitor
npm install
```

If the `android/` folder is missing (unusual if you cloned this repo with it):

```bash
npx cap add android
```

Apply your URL change in `capacitor.config.ts`, then:

```bash
npm run sync
```

`sync` copies web assets and regenerates the Capacitor config inside the Android project.

---

## 5. Open the project in Android Studio

**Option A — CLI**

```bash
cd mobile/capacitor
npx cap open android
```

**Option B — manual**

1. Open **Android Studio**.
2. **File → Open…**
3. Select the folder: **`mobile/capacitor/android`** (the one that contains `app/`, `build.gradle`, etc.).

Wait for **Gradle sync** to finish. If prompted, install any missing SDK platforms or build tools.

---

## 6. Run on an emulator

1. In Android Studio: **Tools → Device Manager** → create a **Virtual Device** (e.g. Pixel Tablet, API 34) if you do not have one.
2. Start the emulator.
3. Ensure `server.url` uses **`http://10.0.2.2:8000/attendance`** (or your tunneled URL) and Laravel is running on the host on port **8000**.
4. Click the green **Run** ▶ button (or **Run → Run 'app'**).

The app should open and load the attendance page in the WebView.

---

## 7. Run on a physical tablet / phone

1. On the device: enable **Developer options** and **USB debugging**.
2. Connect USB (or use wireless debugging in Android Studio).
3. Set `server.url` to **`http://YOUR_PC_LAN_IP:8000/attendance`** (see §3).
4. Run **`npm run sync`**, then in Android Studio select the device and **Run** ▶.

Same Wi‑Fi as the PC is not strictly required for USB install, but the device must be able to **reach** the Laravel host IP for the WebView to load the page.

---

## 8. Permissions and Android manifest (already configured)

The template enables:

| Item | File / location | Why |
|------|-----------------|-----|
| **Internet** | `android/app/src/main/AndroidManifest.xml` | Load your Laravel URL |
| **Cleartext HTTP** | `android:usesCleartextTraffic="true"` on `<application>` | Allow `http://` during development |
| **Camera** | `CAMERA` permission + optional camera features | WebView can use **getUserMedia** for face attendance |

If you add a **release** build for production over **HTTPS**, you can set `usesCleartextTraffic` to false and rely on HTTPS only.

---

## 9. Build a debug APK (install without Android Studio UI)

From **`mobile/capacitor/android`**:

**macOS / Linux**

```bash
./gradlew assembleDebug
```

**Windows**

```bash
gradlew.bat assembleDebug
```

Output path:

```text
mobile/capacitor/android/app/build/outputs/apk/debug/app-debug.apk
```

Copy `app-debug.apk` to the device and open it to install (you may need to allow “Install unknown apps” for the file manager or browser).

---

## 10. Production / Play Store (short checklist)

1. Host Laravel with a **valid HTTPS** certificate.
2. Set `server.url` in `capacitor.config.ts` to that HTTPS URL (or ship a build that loads only that domain).
3. Set **`cleartext: false`** and remove **`android:usesCleartextTraffic="true"`** when you no longer need HTTP.
4. **Versioning:** bump `versionCode` / `versionName` in `android/app/build.gradle`.
5. **Signing:** configure a **release keystore** in Android Studio (**Build → Generate Signed Bundle / APK** or Gradle signing configs).
6. Prefer **Android App Bundle** (`.aab`) for Play Console uploads.

---

## 11. Troubleshooting

### Blank page or “connection refused”

- Wrong URL in `capacitor.config.ts` — use LAN IP on device, `10.0.2.2` on emulator.
- Laravel not listening on `0.0.0.0` — use `--host=0.0.0.0`.
- PC firewall blocking port **8000**.
- Forgot **`npm run sync`** after editing `capacitor.config.ts`.

### “Cleartext HTTP traffic not permitted”

- Ensure `cleartext: true` in `capacitor.config.ts` and **`android:usesCleartextTraffic="true"`** on `<application>` in `AndroidManifest.xml`, then **`npm run sync`** and rebuild.

### Camera does not open in the WebView

- Confirm **Camera** permission exists in `AndroidManifest.xml` (this repo includes it).
- On the device, check **Settings → Apps → Staff Attendance → Permissions** and allow Camera.
- The site must be served over **HTTPS or localhost-style dev URL**; mixed rules still allow our dev HTTP + cleartext setup.

### Gradle / SDK errors in Android Studio

- **File → Settings → Appearance & Behavior → System Settings → Android SDK** — install **Android SDK Platform** matching `compileSdk` (see `android/variables.gradle` or root `build.gradle`).
- **File → Invalidate Caches** if Gradle behaves oddly after upgrades.

### `npx cap` not found

Run commands from **`mobile/capacitor`** after **`npm install`** so `node_modules/.bin` is used, or use `npx cap` from that directory.

---

## 12. Useful paths (quick reference)

| Path | Contents |
|------|----------|
| `mobile/capacitor/capacitor.config.ts` | **Server URL** and cleartext |
| `mobile/capacitor/android/` | Native Android project |
| `mobile/capacitor/android/app/src/main/AndroidManifest.xml` | Permissions, cleartext |
| `mobile/capacitor/android/app/build/outputs/apk/debug/` | Debug **APK** after `assembleDebug` |

---

## 13. Official references

- [Capacitor — Android](https://capacitorjs.com/docs/android)
- [Capacitor — config](https://capacitorjs.com/docs/config) (`server.url`, `server.cleartext`)
- [Android Studio](https://developer.android.com/studio/intro)

For cross-platform notes (same Laravel URL rules), see **`mobile/README.md`**.
