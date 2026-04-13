@extends('layouts.app')

@section('content')
<style>
    .attendance-tools {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .tool-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: var(--shadow-sm);
    }

    .tool-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }

    .tool-subtitle {
        font-size: 13px;
        color: var(--text-soft);
        margin-bottom: 16px;
    }

    .readonly-box {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
    }

    .camera-stage,
    .preview-stage {
        min-height: 280px;
        border: 1px dashed var(--border-strong);
        border-radius: 18px;
        background: var(--surface-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow: hidden;
        position: relative;
    }

    .camera-inner,
    .preview-inner {
        width: 100%;
        text-align: center;
        position: relative;
    }

    #camera-video,
    #capture-preview {
        width: 100%;
        max-width: 420px;
        height: 320px;
        object-fit: cover;
        border-radius: 14px;
        background: #000;
        display: block;
        margin: 0 auto;
    }

    #capture-preview {
        display: none;
        background: #fff;
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
        border: 2px solid rgba(255, 255, 255, 0.9);
        border-radius: 999px;
        box-shadow: 0 0 0 999px rgba(15, 23, 42, 0.18);
        pointer-events: none;
    }

    .camera-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .scan-result {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 600;
        display: none;
        line-height: 1.6;
    }

    .scan-result.success {
        display: block;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
    }

    .scan-result.error {
        display: block;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .scan-result.info {
        display: block;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .attendance-empty {
        padding: 18px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-soft);
    }

    .camera-placeholder {
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.6;
        text-align: center;
    }

    .face-note {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--border);
        color: var(--text-soft);
        font-size: 13px;
        line-height: 1.6;
    }

    .auto-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #f8fafc;
        border: 1px solid var(--border);
        color: var(--text-soft);
    }

    .auto-badge.running {
        background: #ecfdf3;
        border-color: #bbf7d0;
        color: #15803d;
    }

    @media (max-width: 1000px) {
        .attendance-tools {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-header">
    <div>
        <div class="page-heading">Điểm Danh Khuôn Mặt</div>
        <div class="section-subtitle">Tự động quét khuôn mặt theo khung hình và lưu điểm danh theo sự kiện</div>
    </div>
</div>

<form method="GET" action="{{ route('ctxh.face_attendance') }}" class="filters">
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

    <div class="form-group">
        <label>&nbsp;</label>
        <button type="button" id="toggle-auto-scan-header" class="primary-btn" style="width: 100%;">
            Bắt đầu quét tự động
        </button>
    </div>
</form>

<div class="attendance-tools">
    <div class="tool-card">
        <div class="tool-title">Camera nhận diện</div>
        <div class="tool-subtitle">Bật camera và để sinh viên lần lượt đưa khuôn mặt vào khung, hệ thống sẽ tự quét như quét mã</div>

        <div class="camera-stage">
            <div class="camera-inner">
                <video id="camera-video" autoplay playsinline muted></video>
                <div class="face-frame"></div>
                <canvas id="capture-canvas"></canvas>
            </div>
        </div>

        <div class="camera-actions">
            <button type="button" id="toggle-camera" class="primary-btn">
                Bật camera
            </button>

            <button type="button" id="toggle-auto-scan" class="secondary-btn">
                Bắt đầu quét tự động
            </button>
        </div>

        <div id="auto-scan-status" class="auto-badge">
            Chưa bật quét tự động
        </div>

        <div class="face-note">
            Khi bật quét tự động, hệ thống sẽ tự chụp frame theo chu kỳ và gửi lên server để nhận diện. Sinh viên chỉ cần đứng lần lượt trước camera.
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-title">Khung hình gần nhất</div>
        <div class="tool-subtitle">Khung hình được quét gần nhất sẽ hiển thị tại đây</div>

        <div class="preview-stage">
            <div class="preview-inner">
                <img id="capture-preview" alt="Khung hình khuôn mặt gần nhất">
                <div id="preview-placeholder" class="camera-placeholder">
                    Chưa có khung hình nào được gửi để nhận diện.
                    <br><br>
                    Hãy bật camera và bắt đầu quét tự động.
                </div>
            </div>
        </div>

        <div id="scan-result" class="scan-result"></div>
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
                <button type="button" class="status-btn status-present">
                    Có mặt
                </button>
            </div>
        </div>
    @empty
        <div class="attendance-empty" id="attendance-empty">
            Chưa có sinh viên nào được điểm danh cho sự kiện này.
        </div>
    @endforelse
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const eventSelect = document.getElementById('event-select');
        const toggleCameraBtn = document.getElementById('toggle-camera');
        const toggleAutoScanBtn = document.getElementById('toggle-auto-scan');
        const toggleAutoScanHeaderBtn = document.getElementById('toggle-auto-scan-header');
        const autoScanStatus = document.getElementById('auto-scan-status');
        const scanResult = document.getElementById('scan-result');
        const attendanceList = document.getElementById('attendance-list');

        const video = document.getElementById('camera-video');
        const canvas = document.getElementById('capture-canvas');
        const preview = document.getElementById('capture-preview');
        const previewPlaceholder = document.getElementById('preview-placeholder');

        const csrfToken = '{{ csrf_token() }}';
        const attendanceDate = '{{ $selectedDate }}';
        const saveAttendanceUrl = {!! \Illuminate\Support\Js::from(route('ctxh.face_attendance.save')) !!};

        let mediaStream = null;
        let isCameraRunning = false;
        let isAutoScanning = false;
        let submitLock = false;
        let autoScanInterval = null;
        let lastSuccessStudentId = null;
        let lastSuccessAt = 0;

        function showResult(message, type = 'success') {
            scanResult.className = 'scan-result ' + type;
            scanResult.textContent = message;
        }

        function showPreview(imageData) {
            preview.src = imageData;
            preview.style.display = 'block';
            previewPlaceholder.style.display = 'none';
        }

        function resetPreview() {
            preview.src = '';
            preview.style.display = 'none';
            previewPlaceholder.style.display = 'block';
        }

        function renderAttendanceRow(student) {
            const existingRow = attendanceList.querySelector(`[data-student-id="${student.student_id}"]`);

            const rowHtml = `
                <div class="attendance-left">
                    <div class="avatar">${student.initial}</div>
                    <div>
                        <div class="name">${student.name}</div>
                        <div class="student-meta">
                            MSSV: ${student.student_id} · ${student.time}
                        </div>
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
            if (emptyNode) {
                emptyNode.remove();
            }

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

            if (!totalEl || !presentEl || !absentEl) return;

            const presentRows = attendanceList.querySelectorAll('.attendance-row').length;
            const total = parseInt(totalEl.textContent, 10) || 0;

            presentEl.textContent = presentRows;
            absentEl.textContent = Math.max(total - presentRows, 0);
        }

        function updateAutoScanStatus() {
            if (isAutoScanning) {
                autoScanStatus.classList.add('running');
                autoScanStatus.textContent = 'Đang quét tự động';
                toggleAutoScanBtn.textContent = 'Dừng quét tự động';
                toggleAutoScanHeaderBtn.textContent = 'Dừng quét tự động';
            } else {
                autoScanStatus.classList.remove('running');
                autoScanStatus.textContent = 'Chưa bật quét tự động';
                toggleAutoScanBtn.textContent = 'Bắt đầu quét tự động';
                toggleAutoScanHeaderBtn.textContent = 'Bắt đầu quét tự động';
            }
        }

        async function startCamera() {
            if (isCameraRunning) return true;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showResult('Trình duyệt không hỗ trợ mở camera.', 'error');
                return false;
            }

            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    },
                    audio: false
                });

                video.srcObject = mediaStream;
                isCameraRunning = true;
                toggleCameraBtn.textContent = 'Tắt camera';
                showResult('Camera đã bật. Có thể bắt đầu quét tự động.', 'success');
                return true;
            } catch (error) {
                console.error(error);
                showResult('Không thể mở camera. Hãy kiểm tra quyền truy cập camera.', 'error');
                return false;
            }
        }

        function stopCamera() {
            if (!mediaStream) return;

            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
            video.srcObject = null;
            isCameraRunning = false;
            toggleCameraBtn.textContent = 'Bật camera';
            showResult('Camera đã tắt.', 'info');
        }

        function captureCurrentFrame() {
            if (!isCameraRunning || !video.videoWidth || !video.videoHeight) {
                return null;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            return canvas.toDataURL('image/jpeg', 0.85);
        }

        async function scanCurrentFrame() {
            if (submitLock) return;
            if (!isCameraRunning) return;

            const imageData = captureCurrentFrame();

            if (!imageData) {
                return;
            }

            submitLock = true;
            showPreview(imageData);

            try {
                const response = await fetch(saveAttendanceUrl, {
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
                    showResult(result.message || 'Không thể nhận diện và lưu điểm danh.', 'error');
                    submitLock = false;
                    return;
                }

                const now = Date.now();

                if (
                    result.student &&
                    result.student.student_id === lastSuccessStudentId &&
                    now - lastSuccessAt < 5000
                ) {
                    showResult('Đã nhận diện lại cùng sinh viên trong thời gian ngắn, bỏ qua cập nhật lặp.', 'info');
                    submitLock = false;
                    return;
                }

                renderAttendanceRow(result.student);
                updateStats();

                lastSuccessStudentId = result.student.student_id;
                lastSuccessAt = now;

                showResult(result.message, 'success');
            } catch (error) {
                console.error(error);
                showResult('Lỗi kết nối khi gửi ảnh nhận diện.', 'error');
            }

            submitLock = false;
        }

        async function startAutoScan() {
            const started = await startCamera();

            if (!started) {
                return;
            }

            if (isAutoScanning) {
                return;
            }

            isAutoScanning = true;
            updateAutoScanStatus();
            showResult('Đã bật quét tự động. Hệ thống sẽ tự nhận diện theo khung hình.', 'info');

            autoScanInterval = setInterval(() => {
                scanCurrentFrame();
            }, 1500);
        }

        function stopAutoScan() {
            if (autoScanInterval) {
                clearInterval(autoScanInterval);
                autoScanInterval = null;
            }

            isAutoScanning = false;
            updateAutoScanStatus();
            showResult('Đã dừng quét tự động.', 'info');
        }

        if (toggleCameraBtn) {
            toggleCameraBtn.addEventListener('click', async function () {
                if (isCameraRunning) {
                    stopAutoScan();
                    stopCamera();
                } else {
                    await startCamera();
                }
            });
        }

        if (toggleAutoScanBtn) {
            toggleAutoScanBtn.addEventListener('click', async function () {
                if (isAutoScanning) {
                    stopAutoScan();
                } else {
                    await startAutoScan();
                }
            });
        }

        if (toggleAutoScanHeaderBtn) {
            toggleAutoScanHeaderBtn.addEventListener('click', async function () {
                if (isAutoScanning) {
                    stopAutoScan();
                } else {
                    await startAutoScan();
                }
            });
        }

        resetPreview();
        updateAutoScanStatus();
    });
</script>
@endsection