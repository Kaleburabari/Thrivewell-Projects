import { copyFileSync, mkdirSync, statSync } from 'node:fs';
mkdirSync('public/build', { recursive: true });
copyFileSync('public/assets/app.css', 'public/build/app.css');
copyFileSync('public/assets/app.js', 'public/build/app.js');
console.log('ThriveWell assets built to public/build');
