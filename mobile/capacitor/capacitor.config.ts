import type { CapacitorConfig } from '@capacitor/cli';

/**
 * Change `server.url` to where your Laravel app is reachable from the tablet.
 *
 * - Physical device: http://YOUR_PC_LAN_IP:8000/attendance (use php artisan serve --host=0.0.0.0)
 * - Android emulator: http://10.0.2.2:8000/attendance → host machine localhost:8000
 */
const config: CapacitorConfig = {
  appId: 'com.logapp.attendance',
  appName: 'Staff Attendance',
  webDir: 'www',
  server: {
    url: 'http://10.0.2.2:8000/attendance',
    cleartext: true,
  },
};

export default config;
