// PhotoBooth Application
class PhotoBoothApp {
    constructor() {
        this.video = document.getElementById('cameraVideo');
        this.canvas = document.getElementById('cameraCanvas');
        this.ctx = this.canvas.getContext('2d');
        this.photos = [];
        this.currentPhotoCount = 4;
        this.currentCamera = null;
        this.timerDuration = 3;
        this.isCapturing = false;
        this.selectedColor = 'brown';
        this.frameType = 'frame4';
        this.currentStripId = null;
        this.currentStripUrl = null;
        this.retakingIndex = null; // NEW: Index foto yang sedang di-retake

        this.init();
    }

    init() {
        this.setupCameraList();
        this.setupEventListeners();
    }

    async setupCameraList() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            const select = document.getElementById('cameraSelect');
            select.innerHTML = '';
            
            videoDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Camera ${index + 1}`;
                select.appendChild(option);
            });

            if (videoDevices.length > 0) {
                this.currentCamera = videoDevices[0].deviceId;
                this.startCamera();
            }
        } catch (error) {
            console.error('Error getting cameras:', error);
        }
    }

    async startCamera() {
        try {
            if (this.video.srcObject) {
                this.video.srcObject.getTracks().forEach(track => track.stop());
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: this.currentCamera ? { exact: this.currentCamera } : undefined,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            this.video.srcObject = stream;
        } catch (error) {
            console.error('Error starting camera:', error);
            alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
        }
    }

    setupEventListeners() {
        // Camera selection
        document.getElementById('cameraSelect').addEventListener('change', (e) => {
            this.currentCamera = e.target.value;
            this.startCamera();
        });

        // Photo count selection
        document.getElementById('photoCountSelect').addEventListener('change', (e) => {
            this.currentPhotoCount = parseInt(e.target.value);
            this.frameType = `frame${this.currentPhotoCount}`;
            this.updateProgress();
        });

        // Timer selection
        document.getElementById('timerSelect').addEventListener('change', (e) => {
            this.timerDuration = parseInt(e.target.value);
        });

        // Start photo button
        document.getElementById('startPhotoBtn').addEventListener('click', () => {
            this.startPhotoSession();
        });

        // Review modal buttons
        document.getElementById('backBtn').addEventListener('click', () => {
            this.closeReviewModal();
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
            this.downloadPhotoStrip();
        });

        // NEW: Retake button - buka modal pemilihan foto
        document.getElementById('retakeBtn').addEventListener('click', () => {
            this.openRetakeSelection();
        });

        // Save button event listener
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveStripToProfile();
            });
        }

        // Color selector - compose ulang saat ganti warna
        const colorButtons = document.querySelectorAll('.color-btn');
        colorButtons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                colorButtons.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.selectedColor = e.target.dataset.color;
                
                // Update preview lokal
                await this.updateStripPreview();
                
                // Compose ulang di server dengan warna baru
                if (this.photos.length > 0) {
                    await this.composeAndSaveStrip();
                }
            });
        });
    }

    async startPhotoSession() {
        if (this.isCapturing) return;

        // Jika bukan retake, reset semua
        if (this.retakingIndex === null) {
            this.photos = [];
            this.currentStripId = null;
            this.currentStripUrl = null;
        }

        this.isCapturing = true;
        this.updateThumbnails();
        this.updateProgress();

        const btn = document.getElementById('startPhotoBtn');
        btn.disabled = true;

        // Jika retake, hanya ambil 1 foto
        if (this.retakingIndex !== null) {
            await this.capturePhoto(this.retakingIndex + 1);
            
            this.isCapturing = false;
            btn.disabled = false;
            
            // Update thumbnails dan kembali ke review
            this.updateThumbnails();
            this.updateProgress();
            this.closeRetakeModal();
            this.showReviewModal();
            await this.composeAndSaveStrip();
            
            // Reset retaking index
            this.retakingIndex = null;
            btn.querySelector('.btn-text').textContent = 'Mulai Foto';
            return;
        }

        // Proses normal: ambil semua foto
        for (let i = 0; i < this.currentPhotoCount; i++) {
            await this.capturePhoto(i + 1);
            await this.wait(1000);
        }

        this.isCapturing = false;
        btn.disabled = false;
        
        // Tampilkan modal review dengan preview lokal
        this.showReviewModal();
        
        // Compose strip SETELAH modal ditampilkan
        await this.composeAndSaveStrip();
    }

    async capturePhoto(photoNumber) {
        if (this.timerDuration > 0) {
            await this.showCountdown();
        }

        // Flash effect
        const flash = document.getElementById('flashEffect');
        flash.style.display = 'block';
        setTimeout(() => {
            flash.style.display = 'none';
        }, 300);

        // Capture photo
        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;
        this.ctx.drawImage(this.video, 0, 0);
        
        const photoData = this.canvas.toDataURL('image/png');
        
        // Simpan foto: replace jika retake, push jika baru
        if (this.retakingIndex !== null) {
            this.photos[this.retakingIndex] = photoData;
        } else {
            this.photos.push(photoData);
        }

        this.updateThumbnails();
        this.updateProgress();
    }

    async showCountdown() {
        const overlay = document.getElementById('countdownOverlay');
        const number = document.getElementById('countdownNumber');
        
        overlay.style.display = 'flex';

        for (let i = this.timerDuration; i > 0; i--) {
            number.textContent = i;
            number.style.animation = 'none';
            setTimeout(() => {
                number.style.animation = 'countdownPulse 1s ease-in-out';
            }, 10);
            await this.wait(1000);
        }

        overlay.style.display = 'none';
    }

    updateThumbnails() {
        const container = document.getElementById('thumbnailContainer');
        container.innerHTML = '';

        if (this.photos.length === 0) {
            container.innerHTML = '<div class="thumbnail-empty">Foto akan muncul di sini</div>';
            return;
        }

        this.photos.forEach((photo, index) => {
            const div = document.createElement('div');
            div.className = 'thumbnail-item';
            div.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="thumbnail-badge">${index + 1}</div>
            `;
            container.appendChild(div);
        });
    }

    updateProgress() {
        const text = document.getElementById('progressText');
        const fill = document.getElementById('progressFill');
        
        text.textContent = `${this.photos.length}/${this.currentPhotoCount} foto`;
        fill.style.width = `${(this.photos.length / this.currentPhotoCount) * 100}%`;
    }

    // NEW: Buka modal pemilihan foto untuk retake
    openRetakeSelection() {
        const retakeModal = document.getElementById('retakeModal');
        const retakePhotoGrid = document.getElementById('retakePhotoGrid');
        
        retakePhotoGrid.innerHTML = '';
        
        this.photos.forEach((photo, index) => {
            const item = document.createElement('div');
            item.className = 'retake-photo-item';
            item.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="retake-photo-label">Foto ${index + 1}</div>
                <button class="retake-photo-button" data-index="${index}">
                    Ambil Ulang
                </button>
            `;
            
            // Event listener untuk tombol retake
            const btn = item.querySelector('.retake-photo-button');
            btn.addEventListener('click', () => {
                this.retakePhoto(index);
            });
            
            retakePhotoGrid.appendChild(item);
        });
        
        retakeModal.style.display = 'flex';
    }

    // NEW: Retake foto tertentu
    retakePhoto(index) {
        this.retakingIndex = index;
        this.closeRetakeModal();
        this.closeReviewModal();
        
        // Update button text
        const btn = document.getElementById('startPhotoBtn');
        btn.querySelector('.btn-text').textContent = `Ambil Ulang Foto ${index + 1}`;
        
        // Scroll ke kamera
        document.querySelector('.camera-container').scrollIntoView({ behavior: 'smooth' });
        
        // Auto start after 1 second
        setTimeout(() => {
            this.startPhotoSession();
        }, 1000);
    }

    // NEW: Tutup modal retake
    closeRetakeModal() {
        const retakeModal = document.getElementById('retakeModal');
        if (retakeModal) {
            retakeModal.style.display = 'none';
        }
    }

    async composeAndSaveStrip() {
        if (this.photos.length === 0) return;

        try {
            // Tampilkan loading
            const downloadBtn = document.getElementById('downloadBtn');
            const saveBtn = document.getElementById('saveBtn');
            const originalDownloadText = downloadBtn ? downloadBtn.innerHTML : '';
            
            if (downloadBtn) {
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = 'Processing...';
            }

            // Buat canvas dengan frame yang sudah dipilih
            const finalCanvas = await this.createFinalCanvas();
            const finalImageData = finalCanvas.toDataURL('image/png');

            const response = await fetch('/photobooth/compose', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    photos: [finalImageData],
                    frame_id: null,
                    photo_count: this.currentPhotoCount
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.currentStripId = result.strip_id;
                this.currentStripUrl = result.strip_url;
                
                console.log('✅ Strip created:', {
                    id: this.currentStripId,
                    url: this.currentStripUrl
                });
                
                // Tampilkan tombol save jika user sudah login
                this.showSaveButton();
                
                // Enable download button
                if (downloadBtn) {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalDownloadText || 'Download';
                }
            } else {
                console.error('Failed to compose strip:', result.error);
                alert('Gagal membuat photo strip: ' + (result.error || 'Unknown error'));
                
                if (downloadBtn) {
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = originalDownloadText || 'Download';
                }
            }
        } catch (error) {
            console.error('Error composing strip:', error);
            alert('Terjadi kesalahan saat membuat photo strip.');
        }
    }

    async createFinalCanvas() {
        const tempCanvas = document.createElement('canvas');
        const ctx = tempCanvas.getContext('2d');

        // Set canvas dimensions based on photo count
        const canvasWidth = 800;
        let canvasHeight;
        
        switch(this.currentPhotoCount) {
            case 2: canvasHeight = 1600; break;
            case 3: canvasHeight = 2000; break;
            case 4: canvasHeight = 2400; break;
            default: canvasHeight = 2400;
        }

        tempCanvas.width = canvasWidth;
        tempCanvas.height = canvasHeight;

        // Get frame color
        let frameColor;
        switch(this.selectedColor) {
            case 'brown': frameColor = '#6B4423'; break;
            case 'cream': frameColor = '#CBA991'; break;
            case 'white': frameColor = '#F5F5F5'; break;
            default: frameColor = '#6B4423';
        }

        // Draw frame background
        ctx.fillStyle = frameColor;
        ctx.fillRect(0, 0, canvasWidth, canvasHeight);

        // Calculate dimensions for photo slots
        const padding = 60;
        const photoGap = 30;
        const photoWidth = canvasWidth - (padding * 2);
        const availableHeight = canvasHeight - (padding * 2) - (photoGap * (this.currentPhotoCount - 1));
        const photoHeight = availableHeight / this.currentPhotoCount;

        // Draw photos with white borders
        for (let i = 0; i < this.photos.length; i++) {
            const img = await this.loadImage(this.photos[i]);
            
            const xPos = padding;
            const yPos = padding + (i * (photoHeight + photoGap));
            
            // Draw white background/border (10px border)
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(xPos - 10, yPos - 10, photoWidth + 20, photoHeight + 20);
            
            // Draw photo
            ctx.drawImage(img, xPos, yPos, photoWidth, photoHeight);
        }

        // Add frame border effect
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
        ctx.lineWidth = 8;
        ctx.strokeRect(4, 4, canvasWidth - 8, canvasHeight - 8);

        return tempCanvas;
    }

    showSaveButton() {
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn && window.isAuthenticated && this.currentStripId) {
            saveBtn.style.display = 'block';
            saveBtn.disabled = false;
        }
    }

    async saveStripToProfile() {
        if (!this.currentStripId) {
            alert('Strip ID tidak ditemukan! Silakan tunggu hingga proses selesai.');
            return;
        }

        if (!window.isAuthenticated) {
            alert('Anda harus login terlebih dahulu!');
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        
        // Disable button dan ubah text
        saveBtn.disabled = true;
        saveBtn.innerHTML = '⏳ Menyimpan...';

        try {
            const response = await fetch(`/photobooth/save/${this.currentStripId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });

            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                // Redirect ke halaman profil
                window.location.href = '/profile';
            } else {
                alert(result.message);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error saving strip:', error);
            alert('❌ Terjadi kesalahan saat menyimpan.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    showReviewModal() {
        this.updateStripPreview();
        document.getElementById('reviewModal').style.display = 'flex';
    }

    closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    async updateStripPreview() {
        const canvas = document.getElementById('stripCanvas');
        const ctx = canvas.getContext('2d');

        // Set canvas dimensions based on photo count
        const canvasWidth = 360;
        let canvasHeight;
        
        switch(this.currentPhotoCount) {
            case 2: canvasHeight = 800; break;
            case 3: canvasHeight = 1000; break;
            case 4: canvasHeight = 1200; break;
            default: canvasHeight = 1200;
        }

        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        // Get frame color
        let frameColor;
        switch(this.selectedColor) {
            case 'brown': frameColor = '#6B4423'; break;
            case 'cream': frameColor = '#CBA991'; break;
            case 'white': frameColor = '#F5F5F5'; break;
            default: frameColor = '#6B4423';
        }

        // Draw frame background
        ctx.fillStyle = frameColor;
        ctx.fillRect(0, 0, canvasWidth, canvasHeight);

        // Calculate dimensions for photo slots
        const padding = 30;
        const photoGap = 15;
        const photoWidth = canvasWidth - (padding * 2);
        const availableHeight = canvasHeight - (padding * 2) - (photoGap * (this.currentPhotoCount - 1));
        const photoHeight = availableHeight / this.currentPhotoCount;

        // Draw photos with white borders
        for (let i = 0; i < this.photos.length; i++) {
            const img = await this.loadImage(this.photos[i]);
            
            const xPos = padding;
            const yPos = padding + (i * (photoHeight + photoGap));
            
            // Draw white background/border (5px border)
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(xPos - 5, yPos - 5, photoWidth + 10, photoHeight + 10);
            
            // Draw photo
            ctx.drawImage(img, xPos, yPos, photoWidth, photoHeight);
        }

        // Add frame border effect
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
        ctx.lineWidth = 4;
        ctx.strokeRect(2, 2, canvasWidth - 4, canvasHeight - 4);

        // Add subtle shadow for depth
        const gradient = ctx.createLinearGradient(0, 0, 0, canvasHeight);
        gradient.addColorStop(0, 'rgba(0, 0, 0, 0.05)');
        gradient.addColorStop(0.5, 'rgba(0, 0, 0, 0)');
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0.05)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvasWidth, canvasHeight);
    }

    async downloadPhotoStrip() {
        if (this.currentStripId) {
            // Download dari server
            window.location.href = `/photobooth/download/${this.currentStripId}`;
        } else {
            alert('Photo strip belum siap. Silakan tunggu sebentar...');
        }
    }

    loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PhotoBoothApp();
});

// Close login modal function
function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close retake modal function (untuk onclick di HTML)
function closeRetakeModal() {
    const modal = document.getElementById('retakeModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
