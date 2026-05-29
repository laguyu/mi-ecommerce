import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.miecommerce.app',
  appName: 'Mi Ecommerce',
  webDir: 'dist',
  server: {
    // Reemplaza esta URL por tu dominio publico en produccion.
    url: 'https://TU-DOMINIO-PUBLICO.com',
    cleartext: false,
  },
  android: {
    allowMixedContent: false,
    webContentsDebuggingEnabled: true,
  },
};

export default config;
