@extends('layouts.app')

@section('content')
<style>
    .attendance-shell {
        max-width: 1180px;
        margin: 0 auto 24px;
    }

    .attendance-modal {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .attendance-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .attendance-modal__title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f6b51;
    }

    .attendance-modal__close {
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        border-radius: 10px;
        font-size: 32px;
        line-height: 1;
        color: #6b7280;
        cursor: pointer;
    }

    .attendance-modal__body {
        padding: 20px 24px 24px;
    }

    .attendance-main-grid {
        display: grid;
        grid-template-columns: 430px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .attendance-stack {
        display: grid;
        gap: 18px;
    }

    .attendance-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
    }

    .card-title {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 800;
        color: #1f2937;
        text-align: center;
    }

    .card-subtitle {
        margin: 0 0 14px;
        font-size: 14px;
        color: #6b7280;
        text-align: center;
        line-height: 1.6;
    }

    .qr-box-wrap {
        min-height: 250px;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        margin-bottom: 12px;
    }

    #event-qr-box canvas,
    #event-qr-box img {
        margin: 0 auto;
    }

    .center-actions {
        display: flex;
        justify-content: center;
    }

    .mode-switch {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }

    .attendance-tab {
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 700;
        background: #fff;
        color: #2563eb;
        border: 1px solid #cbd5e1;
        cursor: pointer;
    }

    .attendance-tab.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .attendance-status-text {
        color: #6b7280;
        font-size: 15px;
        margin-bottom: 12px;
    }

    .attendance-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    .camera-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 130px;
        height: 42px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #2563eb;
        background: #fff;
        color: #2563eb;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
    }

    .camera-btn.primary {
        background: #2563eb;
        color: #fff;
    }

    .scan-stage {
        min-height: 320px;
        border-radius: 12px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        position: relative;
    }

    .scan-mode-panel {
        width: 100%;
        display: none;
    }

    .scan-mode-panel.active {
        display: block;
    }

    #reader {
        width: 100%;
    }

    #reader video,
    #reader__scan_region video,
    #camera-video,
    #capture-preview {
        width: 100%;
        height: 300px;
        object-fit: cover;
        display: block;
        border-radius: 10px;
        background: #111827;
    }

    .camera-inner,
    .preview-inner {
        width: 100%;
        position: relative;
    }

    #capture-preview {
        display: none;
        background: #f8fafc;
        margin-top: 12px;
    }

    #capture-canvas {
        display: none;
    }

    .face-frame {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 180px;
        height: 220px;
        transform: translate(-50%, -50%);
        border: 2px solid rgba(255, 255, 255, 0.92);
        border-radius: 999px;
        box-shadow: 0 0 0 999px rgba(15, 23, 42, 0.14);
        pointer-events: none;
    }

    .attendance-note,
    .camera-placeholder,
    .qr-placeholder {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
    }

    .camera-placeholder,
    .qr-placeholder {
        text-align: center;
        padding: 24px 12px;
    }

    .scan-result {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        display: none;
        line-height: 1.6;
    }

    .scan-result.success {
        display: block;
        background: #ecfdf3;
        border: 1px solid #bbf7d0;
        color: #15803d;
    }

    .scan-result.error {
        display: block;
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .scan-result.info {
        display: block;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .recognition-card {
        margin-top: 14px;
        display: none;
        padding: 16px;
        border-radius: 12px;
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fecaca;
    }

    .recognition-card.show {
        display: block;
    }

    .recognition-card__title {
        font-size: 15px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .recognition-card__line {
        font-size: 14px;
        line-height: 1.7;
    }

    .recognition-card__line strong {
        font-weight: 800;
    }


    .preview-inner,
    .recognition-card {
        display: none !important;
    }

    .mini-summary {
        margin-top: 10px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        color: #374151;
        font-size: 14px;
    }

    .mini-summary strong.pending {
        color: #ef4444;
    }

    @media (max-width: 992px) {
        .attendance-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .attendance-modal__header,
        .attendance-modal__body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .attendance-modal__title {
            font-size: 18px;
        }

        .mode-switch {
            grid-template-columns: 1fr;
        }

        #reader video,
        #reader__scan_region video,
        #camera-video,
        #capture-preview {
            height: 260px;
        }
    }
</style>

@php
    $eventName = optional($events->firstWhere('maHoatDong', $selectedEventId))->tenHoatDong ?? 'Sự kiện';
@endphp

<div class="attendance-shell">
    <div class="attendance-modal">
        <div class="attendance-modal__header">
            <h2 class="attendance-modal__title">Điểm danh: {{ $eventName }}</h2>
            <button type="button" class="attendance-modal__close" onclick="window.history.back()">×</button>
        </div>

        <div class="attendance-modal__body">
            <form method="GET" action="{{ route('ctxh.attendance') }}" class="filters" style="margin-bottom: 16px;">
                <div class="form-group">
                    <label>Ngày giờ điểm danh</label>
                    <div class="readonly-box">{{ $nowDisplay }}</div>
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                </div>

                <div class="form-group">
                    <label>Chọn sự kiện</label>
                    <select name="event_id" id="event-select" class="form-control" onchange="this.form.submit()">
                        @foreach ($events as $event)
                            <option value="{{ $event->maHoatDong }}" {{ (int) $selectedEventId === (int) $event->maHoatDong ? 'selected' : '' }}>
                                {{ $event->tenHoatDong }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="attendance-main-grid">
                <div class="attendance-stack">
                    <div class="attendance-card">
                        <h3 class="card-title">Mã QR sự kiện</h3>
                        <p class="card-subtitle">Sinh viên dùng ứng dụng quét mã QR bên dưới để điểm danh.</p>
                        <div class="qr-box-wrap">
                            <div id="event-qr-box"></div>
                        </div>
                        <div class="center-actions">
                            <button type="button" id="generate-event-qr" class="camera-btn">⬇ Tải mã QR</button>
                        </div>
                    </div>
                </div>

                <div class="attendance-card">
                    <div class="mode-switch">
                        <button type="button" class="attendance-tab active" data-mode-target="qr-panel">Quét mã sinh viên</button>
                        <button type="button" class="attendance-tab" data-mode-target="face-panel">Quét khuôn mặt</button>
                    </div>

                    <div class="attendance-status-text" id="qr-status-text">Chưa nhận diện mã sinh viên.</div>
                    <div class="attendance-status-text" id="capture-status-text" style="display:none;">Chưa nhận diện khuôn mặt.</div>

                    <div class="attendance-actions" id="qr-actions">
                        <button type="button" id="toggle-camera" class="camera-btn">📷 Mở camera</button>
                    </div>

                    <div class="attendance-actions" id="face-actions" style="display:none;">
                        <button type="button" id="toggle-face-camera" class="camera-btn primary">📷 Mở camera</button>
                        <button type="button" id="toggle-auto-scan" class="camera-btn">Bật quét tự động</button>
                    </div>

                    <div class="scan-stage">
                        <div class="scan-mode-panel active" data-mode-panel="qr-panel">
                            <div id="reader"></div>
                            <div id="qr-placeholder" class="qr-placeholder">Bấm Mở camera để quét mã sinh viên.</div>
                        </div>

                        <div class="scan-mode-panel" data-mode-panel="face-panel">
                            <div class="camera-inner">
                                <video id="camera-video" autoplay playsinline muted></video>
                                <div class="face-frame"></div>
                            </div>
                        </div>
                    </div>

                    <div class="attendance-note" id="shared-note">Phương thức áp dụng: Quét mã sinh viên. Bấm Mở camera để hệ thống quét mã.</div>

                    <div id="qr-scan-result" class="scan-result"></div>
                    <div id="face-scan-result" class="scan-result"></div>

                    <div class="preview-inner">
                        <canvas id="capture-canvas"></canvas>
                        <img id="capture-preview" alt="Ảnh chụp khuôn mặt">
                        <div id="preview-placeholder" class="camera-placeholder" style="display:none;">
                            Chưa có khung hình nào được gửi để nhận diện.<br><br>
                            Hãy bật camera và bắt đầu quét tự động.
                        </div>
                    </div>

                    <div id="recognition-card" class="recognition-card">
                        <div class="recognition-card__title" id="recognition-title">Chưa có kết quả nhận diện.</div>
                        <div class="recognition-card__line">Tên: <strong id="recognition-name">Không xác định</strong></div>
                        <div class="recognition-card__line">MSSV: <strong id="recognition-student-id">Không xác định</strong></div>
                        <div class="recognition-card__line">Lớp: <strong id="recognition-class">Không xác định</strong></div>
                        <div class="recognition-card__line">Ngành/Khoa: <strong id="recognition-department">Không xác định</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-icon {{ $stat['class'] }}">
                {{ $stat['icon'] }}
            </div>
            <div>
                <div class="stat-title">{{ $stat['title'] }}</div>
                <div class="stat-value" id="stat-{{ $stat['key'] }}">{{ $stat['value'] }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="panel-title">Danh sách đã điểm danh</div>

<div class="attendance-list" id="attendance-list">
    @forelse ($students as $student)
        <div class="attendance-row" data-student-id="{{ $student['student_id'] }}">
            <div class="attendance-left">
                <div class="avatar">{{ $student['initial'] }}</div>
                <div>
                    <div class="name">{{ $student['name'] }}</div>
                    <div class="student-meta">
                        MSSV: {{ $student['student_id'] }} · {{ $student['time'] ?? '' }}
                    </div>
                </div>
            </div>
            <div class="attendance-actions">
                <button type="button" class="status-btn status-present">Có mặt</button>
            </div>
        </div>
    @empty
        <div class="attendance-empty" id="attendance-empty">
            Chưa có sinh viên nào được điểm danh cho sự kiện này.
        </div>
    @endforelse
</div>

<div class="attendance-card" style="margin-top: 16px;">
    <div class="mini-summary">
        <div>Tổng: <strong id="summary-total">{{ $stats[0]['value'] ?? 0 }}</strong></div>
        <div>Đã điểm danh: <strong id="summary-present">{{ $stats[1]['value'] ?? 0 }}</strong></div>
        <div>Chưa điểm danh: <strong class="pending" id="summary-absent">{{ $stats[2]['value'] ?? 0 }}</strong></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const eventSelect = document.getElementById('event-select');
        const qrBox = document.getElementById('event-qr-box');
        const generateQrBtn = document.getElementById('generate-event-qr');
        const attendanceList = document.getElementById('attendance-list');

        const modeButtons = document.querySelectorAll('[data-mode-target]');
        const modePanels = document.querySelectorAll('[data-mode-panel]');
        const qrActions = document.getElementById('qr-actions');
        const faceActions = document.getElementById('face-actions');
        const sharedNote = document.getElementById('shared-note');
        const qrPlaceholder = document.getElementById('qr-placeholder');

        const qrStatusText = document.getElementById('qr-status-text');
        const qrScanResult = document.getElementById('qr-scan-result');
        const toggleCameraBtn = document.getElementById('toggle-camera');
        const reader = document.getElementById('reader');

        const faceStatusText = document.getElementById('capture-status-text');
        const faceScanResult = document.getElementById('face-scan-result');
        const toggleFaceCameraBtn = document.getElementById('toggle-face-camera');
        const toggleAutoScanBtn = document.getElementById('toggle-auto-scan');
        const video = document.getElementById('camera-video');
        const canvas = document.getElementById('capture-canvas');
        const preview = document.getElementById('capture-preview');
        const previewPlaceholder = document.getElementById('preview-placeholder');
        const recognitionCard = document.getElementById('recognition-card');
        const recognitionTitle = document.getElementById('recognition-title');
        const recognitionName = document.getElementById('recognition-name');
        const recognitionStudentId = document.getElementById('recognition-student-id');
        const recognitionClass = document.getElementById('recognition-class');
        const recognitionDepartment = document.getElementById('recognition-department');

        const csrfToken = '{{ csrf_token() }}';
        const attendanceDate = '{{ $selectedDate }}';
        const saveQrAttendanceUrl = {!! \Illuminate\Support\Js::from(route('ctxh.attendance.save')) !!};
        const saveFaceAttendanceUrl = {!! \Illuminate\Support\Js::from(route('ctxh.face_attendance.save')) !!};

        let activeMode = 'qr-panel';
        let html5QrCode = null;
        let isQrCameraRunning = false;
        let qrScanLock = false;

        let mediaStream = null;
        let isFaceCameraRunning = false;
        let isAutoScanning = false;
        let faceSubmitLock = false;
        let autoScanInterval = null;
        let lastSuccessStudentId = null;
        let lastSuccessAt = 0;

        function activateMode(target) {
            activeMode = target;

            modeButtons.forEach(button => {
                button.classList.toggle('active', button.dataset.modeTarget === target);
            });

            modePanels.forEach(panel => {
                panel.classList.toggle('active', panel.dataset.modePanel === target);
            });

            const isQrMode = target === 'qr-panel';
            qrActions.style.display = isQrMode ? 'flex' : 'none';
            faceActions.style.display = isQrMode ? 'none' : 'flex';
            qrStatusText.style.display = isQrMode ? 'block' : 'none';
            faceStatusText.style.display = isQrMode ? 'none' : 'block';
            qrScanResult.style.display = isQrMode && qrScanResult.textContent ? 'block' : 'none';
            faceScanResult.style.display = !isQrMode && faceScanResult.textContent ? 'block' : 'none';
            preview.style.display = isQrMode ? 'none' : preview.style.display;
            previewPlaceholder.style.display = isQrMode ? 'none' : (preview.src ? 'none' : 'block');
            recognitionCard.style.display = isQrMode ? 'none' : recognitionCard.classList.contains('show') ? 'block' : 'none';
            sharedNote.textContent = isQrMode
                ? 'Phương thức áp dụng: Quét mã sinh viên. Bấm Mở camera để hệ thống quét mã.'
                : 'Phương thức áp dụng: Quét khuôn mặt. Bấm Mở camera để hệ thống tự chụp.';

            if (isQrMode) {
                stopAutoScan();
                stopFaceCamera();
            } else {
                stopQrScanner();
            }
        }

        function showQrResult(message, type = 'success') {
            qrScanResult.className = 'scan-result ' + type;
            qrScanResult.textContent = message;
            qrScanResult.style.display = activeMode === 'qr-panel' ? 'block' : 'none';
        }

        function showFaceResult(message, type = 'success') {
            faceScanResult.className = 'scan-result ' + type;
            faceScanResult.textContent = message;
            faceScanResult.style.display = activeMode === 'face-panel' ? 'block' : 'none';
        }

        function buildEventPayload() {
            const eventId = eventSelect ? eventSelect.value : '';
            return eventId ? String(eventId) : '';
        }

        function renderEventQr() {
            if (!qrBox) return;
            qrBox.innerHTML = '';

            if (typeof QRCode === 'undefined') {
                qrBox.innerHTML = '<div class="qr-placeholder">Không tải được thư viện tạo QR.</div>';
                return;
            }

            const payload = buildEventPayload();
            if (!payload) {
                qrBox.innerHTML = '<div class="qr-placeholder">Chưa có mã hoạt động để tạo QR.</div>';
                return;
            }

            try {
                new QRCode(qrBox, {
                    text: payload,
                    width: 220,
                    height: 220,
                    correctLevel: QRCode.CorrectLevel.L
                });
            } catch (error) {
                console.error(error);
                qrBox.innerHTML = '<div class="qr-placeholder">Không thể tạo mã QR.</div>';
            }
        }

        function downloadEventQr() {
            const canvasEl = qrBox.querySelector('canvas');
            const img = qrBox.querySelector('img');
            let src = '';

            if (canvasEl) {
                src = canvasEl.toDataURL('image/png');
            } else if (img) {
                src = img.src;
            }

            if (!src) return;

            const link = document.createElement('a');
            link.href = src;
            link.download = 'qr-su-kien-' + (eventSelect ? eventSelect.value : 'event') + '.png';
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        function renderAttendanceRow(student) {
            const existingRow = attendanceList.querySelector(`[data-student-id="${student.student_id}"]`);
            const rowHtml = `
                <div class="attendance-left">
                    <div class="avatar">${student.initial || (student.name ? student.name.charAt(0) : '?')}</div>
                    <div>
                        <div class="name">${student.name || 'Không xác định'}</div>
                        <div class="student-meta">MSSV: ${student.student_id || ''} · ${student.time || ''}</div>
                    </div>
                </div>
                <div class="attendance-actions">
                    <button type="button" class="status-btn status-present">Có mặt</button>
                </div>
            `;

            if (existingRow) {
                existingRow.innerHTML = rowHtml;
                return;
            }

            const emptyNode = document.getElementById('attendance-empty');
            if (emptyNode) emptyNode.remove();

            const wrapper = document.createElement('div');
            wrapper.className = 'attendance-row';
            wrapper.setAttribute('data-student-id', student.student_id);
            wrapper.innerHTML = rowHtml;
            attendanceList.prepend(wrapper);
        }

        function updateStats() {
            const totalEl = document.getElementById('stat-total');
            const presentEl = document.getElementById('stat-present');
            const absentEl = document.getElementById('stat-absent');
            const summaryTotal = document.getElementById('summary-total');
            const summaryPresent = document.getElementById('summary-present');
            const summaryAbsent = document.getElementById('summary-absent');

            const presentRows = attendanceList.querySelectorAll('.attendance-row').length;
            const total = parseInt((totalEl && totalEl.textContent) || (summaryTotal && summaryTotal.textContent) || 0, 10) || 0;
            const absent = Math.max(total - presentRows, 0);

            if (presentEl) presentEl.textContent = presentRows;
            if (absentEl) absentEl.textContent = absent;
            if (summaryTotal) summaryTotal.textContent = total;
            if (summaryPresent) summaryPresent.textContent = presentRows;
            if (summaryAbsent) summaryAbsent.textContent = absent;
        }

        async function startQrScanner() {
            if (!reader || isQrCameraRunning) return;
            qrPlaceholder.style.display = 'none';

            if (typeof Html5Qrcode === 'undefined') {
                reader.innerHTML = '<div class="qr-placeholder">Không tải được thư viện quét QR.</div>';
                return;
            }

            reader.innerHTML = '';
            html5QrCode = new Html5Qrcode('reader');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 240 },
                    async (decodedText) => {
                        if (qrScanLock) return;
                        qrScanLock = true;
                        qrStatusText.textContent = 'Đã quét được mã, đang điểm danh...';

                        try {
                            const response = await fetch(saveQrAttendanceUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    event_id: eventSelect.value,
                                    date: attendanceDate,
                                    qr_payload: decodedText,
                                }),
                            });

                            const result = await response.json();

                            if (!response.ok || !result.success) {
                                showQrResult(result.message || 'Không thể lưu điểm danh từ mã QR.', 'error');
                                qrStatusText.textContent = 'Chưa nhận diện mã sinh viên.';
                                qrScanLock = false;
                                return;
                            }

                            if (result.student) {
                                renderAttendanceRow(result.student);
                                updateStats();
                            }

                            showQrResult(result.message || 'Điểm danh bằng mã QR thành công.', 'success');
                            qrStatusText.textContent = 'Đã nhận diện mã sinh viên thành công.';
                        } catch (error) {
                            console.error(error);
                            showQrResult('Lỗi kết nối khi gửi dữ liệu quét QR.', 'error');
                            qrStatusText.textContent = 'Chưa nhận diện mã sinh viên.';
                        }

                        setTimeout(() => {
                            qrScanLock = false;
                        }, 1200);
                    }
                );

                isQrCameraRunning = true;
                toggleCameraBtn.textContent = '📷 Tắt camera';
                showQrResult('Camera quét QR đã bật.', 'info');
            } catch (error) {
                console.error(error);
                qrPlaceholder.style.display = 'block';
                showQrResult('Không thể mở camera quét QR.', 'error');
            }
        }

        async function stopQrScanner() {
            if (!html5QrCode || !isQrCameraRunning) {
                toggleCameraBtn.textContent = '📷 Mở camera';
                return;
            }

            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (error) {
                console.error(error);
            }

            isQrCameraRunning = false;
            html5QrCode = null;
            toggleCameraBtn.textContent = '📷 Mở camera';
            qrStatusText.textContent = 'Chưa nhận diện mã sinh viên.';
            if (reader) reader.innerHTML = '';
            qrPlaceholder.style.display = 'block';
        }

        function showRecognitionCard(title, student = null) {
            recognitionCard.classList.add('show');
            recognitionCard.style.display = activeMode === 'face-panel' ? 'block' : 'none';
            recognitionTitle.textContent = title;
            recognitionName.textContent = student?.name || 'Không xác định';
            recognitionStudentId.textContent = student?.student_id || 'Không xác định';
            recognitionClass.textContent = student?.class_name || student?.class || 'Không xác định';
            recognitionDepartment.textContent = student?.department || student?.faculty || student?.major || 'Không xác định';
        }

        function showPreview(imageData) {
            preview.src = imageData;
            if (activeMode === 'face-panel') {
                preview.style.display = 'block';
            }
            previewPlaceholder.style.display = 'none';
        }

        function resetPreview() {
            preview.src = '';
            preview.style.display = 'none';
            previewPlaceholder.style.display = activeMode === 'face-panel' ? 'block' : 'none';
        }

        async function startFaceCamera() {
            if (isFaceCameraRunning) return true;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showFaceResult('Trình duyệt không hỗ trợ mở camera.', 'error');
                showRecognitionCard('Không thể mở camera để nhận diện.');
                return false;
            }

            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = mediaStream;
                isFaceCameraRunning = true;
                toggleFaceCameraBtn.textContent = '📷 Tắt camera';
                showFaceResult('Camera khuôn mặt đã bật.', 'success');
                return true;
            } catch (error) {
                console.error(error);
                showFaceResult('Không thể mở camera. Hãy kiểm tra quyền truy cập camera.', 'error');
                showRecognitionCard('Không thể mở camera.');
                return false;
            }
        }

        function stopFaceCamera() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
            }
            mediaStream = null;
            video.srcObject = null;
            isFaceCameraRunning = false;
            toggleFaceCameraBtn.textContent = '📷 Mở camera';
        }

        function captureCurrentFrame() {
            if (!isFaceCameraRunning || !video.videoWidth || !video.videoHeight) return null;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.85);
        }

        async function scanCurrentFrame() {
            if (faceSubmitLock || !isFaceCameraRunning) return;
            const imageData = captureCurrentFrame();
            if (!imageData) return;

            faceSubmitLock = true;
            showPreview(imageData);
            faceStatusText.textContent = 'Đã chụp khuôn mặt, đang điểm danh...';

            try {
                const response = await fetch(saveFaceAttendanceUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: eventSelect.value,
                        date: attendanceDate,
                        image_data: imageData,
                    }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    showFaceResult(result.message || 'Không thể nhận diện và lưu điểm danh.', 'error');
                    showRecognitionCard(result.message || 'Không nhận diện được sinh viên này.');
                    faceSubmitLock = false;
                    return;
                }

                const now = Date.now();
                if (result.student && result.student.student_id === lastSuccessStudentId && now - lastSuccessAt < 5000) {
                    showFaceResult('Đã nhận diện lại cùng sinh viên trong thời gian ngắn, bỏ qua cập nhật lặp.', 'info');
                    showRecognitionCard('Sinh viên đã được nhận diện trước đó.', result.student);
                    faceSubmitLock = false;
                    return;
                }

                if (result.student) {
                    renderAttendanceRow(result.student);
                    updateStats();
                    lastSuccessStudentId = result.student.student_id;
                    lastSuccessAt = now;
                }

                showFaceResult(result.message || 'Nhận diện thành công.', 'success');
                showRecognitionCard(result.message || 'Nhận diện thành công.', result.student || null);
            } catch (error) {
                console.error(error);
                showFaceResult('Lỗi kết nối khi gửi ảnh nhận diện.', 'error');
                showRecognitionCard('Lỗi kết nối khi gửi ảnh nhận diện.');
            }

            faceSubmitLock = false;
        }

        async function startAutoScan() {
            const started = await startFaceCamera();
            if (!started || isAutoScanning) return;
            isAutoScanning = true;
            toggleAutoScanBtn.textContent = 'Dừng quét tự động';
            faceStatusText.textContent = 'Đang quét khuôn mặt tự động...';
            showFaceResult('Đã bật quét tự động.', 'info');
            autoScanInterval = setInterval(scanCurrentFrame, 1500);
        }

        function stopAutoScan() {
            if (autoScanInterval) {
                clearInterval(autoScanInterval);
                autoScanInterval = null;
            }
            isAutoScanning = false;
            toggleAutoScanBtn.textContent = 'Bật quét tự động';
            faceStatusText.textContent = 'Chưa nhận diện khuôn mặt.';
        }

        modeButtons.forEach(button => {
            button.addEventListener('click', function () {
                activateMode(button.dataset.modeTarget);
            });
        });

        if (generateQrBtn) {
            generateQrBtn.addEventListener('click', downloadEventQr);
        }

        if (toggleCameraBtn) {
            toggleCameraBtn.addEventListener('click', async function () {
                if (isQrCameraRunning) {
                    await stopQrScanner();
                    showQrResult('Đã tắt camera quét QR.', 'info');
                } else {
                    await startQrScanner();
                }
            });
        }

        if (toggleFaceCameraBtn) {
            toggleFaceCameraBtn.addEventListener('click', async function () {
                if (isFaceCameraRunning) {
                    stopAutoScan();
                    stopFaceCamera();
                    showFaceResult('Camera đã tắt.', 'info');
                } else {
                    await startFaceCamera();
                }
            });
        }

        if (toggleAutoScanBtn) {
            toggleAutoScanBtn.addEventListener('click', async function () {
                if (isAutoScanning) {
                    stopAutoScan();
                    showFaceResult('Đã dừng quét tự động.', 'info');
                } else {
                    await startAutoScan();
                }
            });
        }

        renderEventQr();
        updateStats();
        resetPreview();
        activateMode('qr-panel');
    });
</script>
@endsection
