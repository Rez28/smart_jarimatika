/**
 * JARIMATIKA CORE (WITH SMART CROP + FPS MONITORING) - IMPROVED LANDMARKS
 * Palette: Green #BBCB64, Yellow #FFE52A, Orange #F79A19, Red #CF0F0F
 */

const videoElement = document.getElementsByClassName("input_video")[0];
const canvasElement = document.getElementsByClassName("output_canvas")[0];
const canvasCtx = canvasElement.getContext("2d");

// Variabel untuk menyimpan koordinat landmark terakhir (untuk cropping)
let lastLandmarks = [];

// === PERFORMANCE MONITORING ===
let frameCount = 0;
let lastFpsUpdate = 0;
let currentFps = 0;

window.gameState = {
    isSystemReady: false,
    detectedNumber: 0,
    leftValue: 0,
    rightValue: 0,
    leftFingersRaw: [0, 0, 0, 0, 0],
    rightFingersRaw: [0, 0, 0, 0, 0],
    currentFps: 0,
};

// --- LOGIKA UTAMA ---
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

    // === FPS CALCULATION ===
    const now = performance.now();
    frameCount++;

    if (now - lastFpsUpdate >= 1000) {
        currentFps = Math.round((frameCount * 1000) / (now - lastFpsUpdate));
        lastFpsUpdate = now;
        frameCount = 0;
        window.gameState.currentFps = currentFps;
    }

    // Clear canvas
    canvasCtx.save();
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    
    // Mirror untuk video (supaya natural seperti cermin)
    canvasCtx.scale(-1, 1);
    canvasCtx.translate(-canvasElement.width, 0);
    
    // Draw video frame
    canvasCtx.drawImage(
        results.image,
        0,
        0,
        canvasElement.width,
        canvasElement.height,
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

            // === IMPROVED LANDMARK VISUALIZATION ===
            // Gambar Skeleton dengan garis lebih halus
            drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {
                color: "#00FF00",
                lineWidth: 3,
                radius: 3
            });
            
            // Gambar Landmarks dengan dot yang lebih kecil dan clean
            drawLandmarks(canvasCtx, landmarks, {
                color: "#FF0000",
                lineWidth: 1,
                radius: 4
            });
            // ========================================

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

    // === DRAW OVERLAY TEXT (TIDAK DI-MIRROR) ===
    // Reset transform ke normal sebelum gambar text
    canvasCtx.save();
    canvasCtx.setTransform(1, 0, 0, 1, 0, 0);
    
    // Background untuk text agar lebih terbaca
    canvasCtx.fillStyle = "rgba(0, 0, 0, 0.7)";
    canvasCtx.fillRect(10, 10, 140, 55);
    
    // Draw FPS
    canvasCtx.fillStyle = "#00FF00";
    canvasCtx.font = "bold 16px Fredoka, sans-serif";
    canvasCtx.fillText(`FPS: ${currentFps}`, 20, 32);

    // Draw Hands count
    if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
        canvasCtx.fillStyle = "#FFFF00";
        canvasCtx.fillText(
            `Hands: ${results.multiHandLandmarks.length}`,
            20,
            54
        );
    }
    
    canvasCtx.restore();
    // =============================================

    window.gameState.detectedNumber = leftNum + rightNum;
    window.gameState.leftValue = leftNum;
    window.gameState.rightValue = rightNum;
    window.gameState.leftFingersRaw = leftFingersRaw;
    window.gameState.rightFingersRaw = rightFingersRaw;

    const userDisplay = document.getElementById("user-current-answer");
    if (userDisplay) userDisplay.innerText = window.gameState.detectedNumber;
}

// === SMART CROP ===
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
    const padding = 90;
    const width = canvasElement.width;
    const height = canvasElement.height;

    // Karena canvas di-mirror (scaleX -1), koordinat X harus dibalik logikanya
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
    tempCtx.drawImage(
        canvasElement,
        pixelMinX,
        pixelMinY,
        cropW,
        cropH,
        0,
        0,
        cropW,
        cropH,
    );

    return tempCanvas.toDataURL("image/png");
};

// === INISIALISASI MEDIAPIPE ===
const hands = new Hands({
    locateFile: (file) =>
        `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`,
});
hands.setOptions({
    maxNumHands: 2,
    modelComplexity: 1, // Tingkatkan ke 1 untuk akurasi lebih baik
    minDetectionConfidence: 0.7, // Tingkatkan confidence
    minTrackingConfidence: 0.7,
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

window.startCameraSystem = function () {
    console.log("System: Kamera Dinyalakan");
    camera.start();
};

window.stopCameraSystem = function () {
    console.log("System: Kamera Dimatikan");
    camera.stop();

    if (canvasCtx && canvasElement) {
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        canvasCtx.fillStyle = "#000000";
        canvasCtx.fillRect(0, 0, canvasElement.width, canvasElement.height);
    }

    window.gameState.detectedNumber = 0;
    const userDisplay = document.getElementById("user-current-answer");
    if (userDisplay) userDisplay.innerText = "0";
};