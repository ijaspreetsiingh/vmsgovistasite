// One-off script: normalize hero images to 16:9 (1920x1080) true-webp.
// Fixes: mislabeled extensions (dub1/k/thailand are PNG, kas is JPEG)
// and the portrait kas.webp (0.75 ratio) that breaks cover-fit on laptop screens.
const sharp = require('sharp');
const files = ['dub1', 'k', 'thailand', 'kas'];

(async () => {
  for (const name of files) {
    const src = `assets/hero/${name}.webp`;
    // Center-crop to 16:9 then export real webp q80
    await sharp(src)
      .resize(1920, 1080, { fit: 'cover', position: 'center' })
      .webp({ quality: 80 })
      .toFile(`assets/hero/${name}-16x9.webp`);
    const meta = await sharp(`assets/hero/${name}-16x9.webp`).metadata();
    console.log(`${name}: ${meta.width}x${meta.height} format=${meta.format} (${(require('fs').statSync(`assets/hero/${name}-16x9.webp`).size / 1024 / 1024).toFixed(2)}MB)`);
  }
})();
