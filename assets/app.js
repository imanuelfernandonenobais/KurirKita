'use strict';

/**
 * Interaksi kecil tanpa dependensi:
 * - sidebar mobile
 * - konfirmasi hapus
 * - tampil/sembunyi password
 * - salin nomor resi
 */
(function initKirimKita() {
  const body = document.body;

  document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => body.classList.toggle('sidebar-open'));
  });

  document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => body.classList.remove('sidebar-open'));
  });

  document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!window.confirm(button.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.parentElement.querySelector('input');
      if (!input) return;

      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? '◌' : '◉';
      button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      const value = button.dataset.copy;

      try {
        await navigator.clipboard.writeText(value);
        const original = button.innerHTML;
        button.textContent = 'Tersalin!';
        window.setTimeout(() => { button.innerHTML = original; }, 1600);
      } catch (error) {
        // Browser non-HTTPS tertentu tidak menyediakan Clipboard API.
        window.prompt('Salin teks ini:', value);
      }
    });
  });
})();

/** Form akun: tampilkan pemilihan kurir hanya untuk role kurir. */
(function initRoleSelector() {
  const role = document.querySelector('[data-user-role]');
  const courierField = document.querySelector('[data-courier-selector]');

  if (!role || !courierField) return;

  const courierSelect = courierField.querySelector('select');
  const syncField = () => {
    const isCourier = role.value === 'courier';
    courierField.hidden = !isCourier;
    if (courierSelect) {
      courierSelect.disabled = !isCourier;
      courierSelect.required = isCourier;
    }
  };

  role.addEventListener('change', syncField);
  syncField();
})();

/** Cetak label pengiriman yang telah disiapkan sebagai halaman khusus. */
document.querySelectorAll('[data-print]').forEach((button) => {
  button.addEventListener('click', () => window.print());
});

/**
 * Scanner kamera tanpa dependensi: memakai API BarcodeDetector jika tersedia.
 * Scanner keyboard/manual tetap dapat digunakan bila API tidak didukung.
 */
(function initCameraScanner() {
  const root = document.querySelector('[data-camera-scanner]');
  if (!root) return;

  const startButton = root.querySelector('[data-camera-start]');
  const stopButton = root.querySelector('[data-camera-stop]');
  const preview = root.querySelector('[data-camera-preview]');
  const video = root.querySelector('[data-camera-video]');
  const message = root.querySelector('[data-camera-message]');
  const searchForm = root.closest('.panel').querySelector('form.tracking-search');
  const resiInput = searchForm?.querySelector('input[name="resi"]');

  let stream = null;
  let frame = null;
  let scanning = false;
  let detecting = false;
  let lastDetection = 0;

  function stopScanner() {
    scanning = false;
    if (frame !== null) window.cancelAnimationFrame(frame);
    frame = null;
    if (stream) stream.getTracks().forEach((track) => track.stop());
    stream = null;
    video.srcObject = null;
    preview.hidden = true;
    stopButton.hidden = true;
    startButton.disabled = false;
  }

  async function detectFrame(detector, now) {
    if (!scanning) return;
    if (!detecting && now - lastDetection > 230 && video.readyState >= 2) {
      lastDetection = now;
      detecting = true;
      try {
        const codes = await detector.detect(video);
        if (!scanning) return;
        const code = codes.find((item) => typeof item.rawValue === 'string' && /^KK[A-Z0-9]{8,30}$/i.test(item.rawValue.trim()));
        if (code && resiInput && searchForm) {
          resiInput.value = code.rawValue.trim().toUpperCase();
          message.textContent = 'Nomor resi terbaca. Membuka detail paket…';
          stopScanner();
          searchForm.requestSubmit();
          return;
        }
      } catch (error) {
        message.textContent = 'Kamera terbuka, arahkan barcode/QR ke kamera.';
      } finally {
        detecting = false;
      }
    }
    frame = window.requestAnimationFrame((timestamp) => detectFrame(detector, timestamp));
  }

  startButton.addEventListener('click', async () => {
    if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
      message.textContent = 'Browser ini tidak mendukung scan kamera. Gunakan Chrome/Edge terbaru melalui localhost atau HTTPS, atau ketik resi secara manual.';
      resiInput?.focus();
      return;
    }

    startButton.disabled = true;
    message.textContent = 'Meminta izin kamera…';
    try {
      const formats = await window.BarcodeDetector.getSupportedFormats();
      const supported = ['qr_code', 'code_128', 'code_39'].filter((format) => formats.includes(format));
      if (!supported.length) throw new Error('Format QR/barcode tidak didukung di browser ini.');
      const detector = new window.BarcodeDetector({ formats: supported });
      stream = await navigator.mediaDevices.getUserMedia({ audio: false, video: { facingMode: { ideal: 'environment' } } });
      video.srcObject = stream;
      preview.hidden = false;
      stopButton.hidden = false;
      await video.play();
      scanning = true;
      message.textContent = 'Arahkan QR atau barcode berisi nomor resi ke kamera.';
      frame = window.requestAnimationFrame((timestamp) => detectFrame(detector, timestamp));
    } catch (error) {
      stopScanner();
      message.textContent = `Tidak dapat memulai scan kamera: ${error.message}. Masukkan resi secara manual.`;
    }
  });

  stopButton.addEventListener('click', () => {
    stopScanner();
    message.textContent = 'Kamera dimatikan. Anda tetap bisa memasukkan nomor resi secara manual.';
  });
  window.addEventListener('pagehide', stopScanner);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopScanner();
  });
})();
