/**
 * JARIMATIKA CORE (WITH SMART CROP)
 */

const videoElement = document.getElementsByClassName("input_video")[0];
const canvasElement = document.getElementsByClassName("output_canvas")[0];
const canvasCtx = canvasElement.getContext("2d");

// Variabel untuk menyimpan koordinat landmark terakhir (untuk cropping)
let lastLandmarks = [];

window.gameState = {
    isSystemReady: false,
    detectedNumber: 0,
    leftValue: 0,
    rightValue: 0,
    leftFingersRaw: [0, 0, 0, 0, 0],
    rightFingersRaw: [0, 0, 0, 0, 0],
};

// --- LOGIKA UTAMA (Sama seperti sebelumnya) ---
function getFingerState(landmarks, label) {
    let state = [];
    if (label === "Right") {
        if (landmarks[4].x < landmarks[3].x) state.push(1);
        else state.push(0);
    } else {
        if (landmarks[4].x > landmarks[3].x) state.push(1);
        else state.push(0);
    }
    const tips = [8, 12, 16, 20];
    const pips = [6, 10, 14, 18];
    for (let i = 0; i < tips.length; i++) {
        if (landmarks[tips[i]].y < landmarks[pips[i]].y) state.push(1);
        else state.push(0);
    }
    return state;
}

function getJarimatikaNumber(fingers) {
    const code = fingers.join("");
    const patterns = {
        "01000": 1,
        "01100": 2,
        "01110": 3,
        "01111": 4,
        10000: 5,
        11000: 6,
        11100: 7,
        11110: 8,
        11111: 9,
    };
    return patterns[code] || 0;
}

function onResults(results) {
    if (!window.gameState.isSystemReady) window.gameState.isSystemReady = true;

    canvasCtx.save();
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    canvasCtx.scale(-1, 1);
    canvasCtx.translate(-canvasElement.width, 0);
    canvasCtx.drawImage(
        results.image,
        0,
        0,
        canvasElement.width,
        canvasElement.height
    );

    let rightNum = 0,
        leftNum = 0;
    let rightFingersRaw = [0, 0, 0, 0, 0],
        leftFingersRaw = [0, 0, 0, 0, 0];

    // Reset landmarks
    lastLandmarks = [];

    if (results.multiHandLandmarks && results.multiHandedness) {
        // Simpan semua landmark yang terdeteksi untuk keperluan cropping nanti
        lastLandmarks = results.multiHandLandmarks;

        for (
            let index = 0;
            index < results.multiHandLandmarks.length;
            index++
        ) {
            const classification = results.multiHandedness[index];
            const landmarks = results.multiHandLandmarks[index];

            // Gambar Skeleton
            drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {
                color: "#00FF00",
                lineWidth: 2,
            });
            drawLandmarks(canvasCtx, landmarks, {
                color: "#FF0000",
                lineWidth: 1,
            });

            const fingers = getFingerState(landmarks, classification.label);
            const num = getJarimatikaNumber(fingers);

            if (classification.label === "Right") {
                leftNum = num * 10;
                leftFingersRaw = fingers;
            } else {
                rightNum = num;
                rightFingersRaw = fingers;
            }
        }
    }
    canvasCtx.restore();

    window.gameState.detectedNumber = leftNum + rightNum;
    window.gameState.leftValue = leftNum;
    window.gameState.rightValue = rightNum;
    window.gameState.leftFingersRaw = leftFingersRaw;
    window.gameState.rightFingersRaw = rightFingersRaw;

    const userDisplay = document.getElementById("user-current-answer");
    if (userDisplay) userDisplay.innerText = window.gameState.detectedNumber;
}

// === FITUR BARU: SMART CROP ===
// Fungsi ini dipanggil oleh jarimatika-game.js saat jawaban benar
window.captureHandSmart = function () {
    if (lastLandmarks.length === 0) return null;

    // 1. Cari Bounding Box (Kotak Terluar) dari semua tangan yang terlihat
    let minX = 1,
        minY = 1,
        maxX = 0,
        maxY = 0;

    lastLandmarks.forEach((hand) => {
        hand.forEach((point) => {
            if (point.x < minX) minX = point.x;
            if (point.x > maxX) maxX = point.x;
            if (point.y < minY) minY = point.y;
            if (point.y > maxY) maxY = point.y;
        });
    });

    // 2. Konversi ke Pixel & Tambah Padding
    const padding = 90; // Jarak aman (px) agar jari tidak terpotong pas
    const width = canvasElement.width;
    const height = canvasElement.height;

    // Karena canvas di-mirror (scaleX -1), koordinat X harus dibalik logikanya
    // Rumus mirror: realX = width - (x * width)
    let pixelMinX = width - maxX * width - padding;
    let pixelMaxX = width - minX * width + padding;
    let pixelMinY = minY * height - padding;
    let pixelMaxY = maxY * height + padding;

    // Validasi agar tidak keluar canvas
    if (pixelMinX < 0) pixelMinX = 0;
    if (pixelMinY < 0) pixelMinY = 0;
    let cropW = pixelMaxX - pixelMinX;
    let cropH = pixelMaxY - pixelMinY;
    if (pixelMinX + cropW > width) cropW = width - pixelMinX;
    if (pixelMinY + cropH > height) cropH = height - pixelMinY;

    // 3. Buat Canvas Sementara untuk Crop
    const tempCanvas = document.createElement("canvas");
    tempCanvas.width = cropW;
    tempCanvas.height = cropH;
    const tempCtx = tempCanvas.getContext("2d");

    // 4. Gambar hanya bagian yang dipilih dari canvas utama
    // Kita ambil data langsung dari canvasElement yang sudah ada gambar videonya
    tempCtx.drawImage(
        canvasElement,
        pixelMinX,
        pixelMinY,
        cropW,
        cropH, // Source (x,y,w,h)
        0,
        0,
        cropW,
        cropH // Dest (x,y,w,h)
    );

    return tempCanvas.toDataURL("image/png");
};

// Inisialisasi MediaPipe
// ... (Kode fungsi onResults dan lainnya di atas TETAP SAMA) ...

// === INISIALISASI MEDIAPIPE (JANGAN AUTO START) ===
const hands = new Hands({
    locateFile: (file) =>
        `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`,
});
hands.setOptions({
    maxNumHands: 2,
    modelComplexity: 0,
    minDetectionConfidence: 0.5,
    minTrackingConfidence: 0.5,
});
hands.onResults(onResults);

const camera = new Camera(videoElement, {
    onFrame: async () => {
        await hands.send({ image: videoElement });
    },
    width: 640,
    height: 480,
});

// === FUNGSI KONTROL KAMERA (Global Access) ===

// Fungsi Nyalakan
window.startCameraSystem = function () {
    console.log("System: Kamera Dinyalakan");
    camera.start();
};

// Fungsi Matikan
window.stopCameraSystem = function () {
    console.log("System: Kamera Dimatikan");
    // 1. Stop stream
    camera.stop();

    // 2. Bersihkan Canvas (Jadi Hitam)
    if (canvasCtx && canvasElement) {
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        canvasCtx.fillStyle = "#000000";
        canvasCtx.fillRect(0, 0, canvasElement.width, canvasElement.height);
    }

    // 3. Reset Deteksi
    window.gameState.detectedNumber = 0;
    const userDisplay = document.getElementById("user-current-answer");
    if (userDisplay) userDisplay.innerText = "0";
};

// PENTING: JANGAN ADA BARIS "camera.start()" DI SINI!
// Biarkan fungsi window.startCameraSystem yang dipanggil oleh tombol nanti.

window.startCameraSystem = function () {
    console.log("Kamera Dinyalakan");
    camera.start();
};

// === TAMBAHAN BARU: FUNGSI STOP ===
window.stopCameraSystem = function () {
    console.log("Kamera Dimatikan");
    // 1. Matikan MediaPipe Camera Utils
    camera.stop();

    // 2. Bersihkan Canvas (agar layar jadi hitam/bersih, tidak freeze di frame terakhir)
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    canvasCtx.fillStyle = "#2d3436"; // Warna background gelap
    canvasCtx.fillRect(0, 0, canvasElement.width, canvasElement.height);

    // 3. Reset deteksi ke 0 agar game tidak mendeteksi hantu
    window.gameState.detectedNumber = 0;
    const userDisplay = document.getElementById("user-current-answer");
    if (userDisplay) userDisplay.innerText = "0";
};
