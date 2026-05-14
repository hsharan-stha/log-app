# Staff attendance — tablet / phone apps (WebView)

This wraps your Laravel **Attendance** page (`/attendance`) in a native **Android** and **iOS** shell using [Capacitor](https://capacitorjs.com/) (full-screen WebView).

## Important: `localhost` does not work on a real tablet

On the device, `localhost` is the tablet itself, not your PC.

1. Run Laravel on your LAN:

   `php artisan serve --host=0.0.0.0 --port=8000`

2. Note your computer’s IP (e.g. `192.168.1.42`).

3. Edit **`mobile/capacitor/capacitor.config.ts`** → `server.url`, e.g.  
   `http://192.168.1.42:8000/attendance`

4. From **`mobile/capacitor`** run: `npm run sync`

**Android emulator only:** `http://10.0.2.2:8000/attendance` reaches the host machine’s `localhost:8000`.

---

## One-time setup

```bash
cd mobile/capacitor
npm install
```

If `android/` or `ios/` is missing:

```bash
npx cap add android
npx cap add ios   # macOS only
```

After you change **`capacitor.config.ts`**:

```bash
npm run sync
```

---

## Android (tablet / phone)

**Full guide:** **[`mobile/ANDROID.md`](ANDROID.md)** (prerequisites, URL setup, emulator vs device, APK build, troubleshooting).

Quick start:

```bash
cd mobile/capacitor
npm install
npm run sync
npx cap open android
```

In Android Studio: choose a device or emulator → **Run**.  
Cleartext HTTP and **camera** permission are enabled for the attendance page.

---

## iOS / iPad (Mac + Xcode)

1. Install **Xcode** (App Store).
2. Install **CocoaPods** (pick one):
   - **Homebrew (recommended):** `brew install cocoapods`  
     On Apple Silicon, `pod` is usually at `/opt/homebrew/bin/pod`. If your shell says `command not found`, open a **new terminal tab** or run:  
     `export PATH="/opt/homebrew/bin:$PATH"`
   - **Ruby gem:** `sudo gem install cocoapods` (older setups)
3. Install pods:

```bash
cd mobile/capacitor/ios/App
pod install
```

4. Open **`mobile/capacitor/ios/App/App.xcworkspace`** in Xcode (not the `.xcodeproj`).
5. Select iPad or simulator → **Run**.

`Info.plist` includes **camera** usage text and relaxed **App Transport Security** for local HTTP during development. Tighten ATS before an App Store release.

---

## Production

Use **HTTPS** on a real domain, point `server.url` there, and remove or tighten cleartext / ATS rules before store submission.
