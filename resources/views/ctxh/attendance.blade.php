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

    .qr-stage,
    .scan-stage {
        min-height: 280px;
        border: 1px dashed var(--border-strong);
        border-radius: 18px;
        background: var(--surface-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .qr-stage-inner {
        width: 100%;
        text-align: center;
    }

    #event-qr-box canvas,
    #event-qr-box img {
        margin: 0 auto;
    }

    #reader {
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
    }

    .scan-result {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 600;
        display: none;
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

    .attendance-empty {
        padding: 18px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-soft);
    }

    .qr-placeholder {
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.6;
        text-align: center;
    }

    @media (max-width: 1000px) {
        .attendance-tools {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-header">
    <div>
        <div class="page-heading">Điểm Danh</div>
        <div class="section-subtitle">Quét mã QR sinh viên để lưu điểm danh theo sự kiện</div>
    </div>
</div>

<form method="GET" action="{{ route('ctxh.attendance') }}" class="filters">
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
        <button type="button" id="generate-event-qr" class="primary-btn" style="width: 100%;">
            Tạo QR sự kiện
        </button>
    </div>
</form>

<div class="attendance-tools">
    <div class="tool-card">
        <div class="tool-title">QR của sự kiện</div>
        <div class="tool-subtitle">Mã QR này đại diện cho mã hoạt động đang chọn</div>

        <div class="qr-stage">
            <div class="qr-stage-inner">
                <div id="event-qr-box"></div>
            </div>
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-title">Khung quét mã sinh viên</div>
        <div class="tool-subtitle">Quét QR của sinh viên để tự động lưu điểm danh</div>

        <div style="margin-bottom: 12px;">
            <button type="button" id="toggle-camera" class="primary-btn">
                Bật camera
            </button>
        </div>

        <div class="scan-stage">
            <div style="width: 100%;">
                <div id="reader"></div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const eventSelect = document.getElementById('event-select');
        const qrBox = document.getElementById('event-qr-box');
        const generateQrBtn = document.getElementById('generate-event-qr');
        const toggleCameraBtn = document.getElementById('toggle-camera');
        const scanResult = document.getElementById('scan-result');
        const attendanceList = document.getElementById('attendance-list');
        const reader = document.getElementById('reader');

        const csrfToken = '{{ csrf_token() }}';
        const attendanceDate = '{{ $selectedDate }}';
        const saveAttendanceUrl = {!! \Illuminate\Support\Js::from(route('ctxh.attendance.save')) !!};

        let html5QrCode = null;
        let isCameraRunning = false;
        let scanLock = false;

        function showResult(message, type = 'success') {
            scanResult.className = 'scan-result ' + type;
            scanResult.textContent = message;
        }

        function buildEventPayload() {
            const eventId = eventSelect ? eventSelect.value : '';
            if (!eventId) return '';
            return String(eventId);
        }

        function renderEventQr() {
            if (!qrBox) return;

            qrBox.innerHTML = '';

            if (typeof QRCode === 'undefined') {
                qrBox.innerHTML = `
                    <div class="qr-placeholder">
                        Không tải được thư viện tạo QR.
                        <br><br>
                        Kiểm tra kết nối mạng hoặc CDN.
                    </div>
                `;
                return;
            }

            const payload = buildEventPayload();

            if (!payload) {
                qrBox.innerHTML = `
                    <div class="qr-placeholder">
                        Chưa có mã hoạt động để tạo QR.
                    </div>
                `;
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
                qrBox.innerHTML = `
                    <div class="qr-placeholder">
                        Không thể tạo mã QR.
                    </div>
                `;
            }
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

        async function startScanner() {
            if (!reader || isCameraRunning) return;

            if (typeof Html5Qrcode === 'undefined') {
                reader.innerHTML = `
                    <div class="qr-placeholder">
                        Không tải được thư viện quét QR.
                        <br><br>
                        Kiểm tra kết nối mạng hoặc CDN.
                    </div>
                `;
                return;
            }

            reader.innerHTML = '';
            html5QrCode = new Html5Qrcode('reader');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: 240
                    },
                    async (decodedText) => {
                        if (scanLock) return;
                        scanLock = true;

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
                                    qr_payload: decodedText,
                                }),
                            });

                            const result = await response.json();

                            if (!response.ok || !result.success) {
                                showResult(result.message || 'Không thể lưu điểm danh.', 'error');
                                scanLock = false;
                                return;
                            }

                            renderAttendanceRow(result.student);
                            updateStats();
                            showResult(result.message, 'success');
                        } catch (error) {
                            console.error(error);
                            showResult('Lỗi kết nối khi lưu điểm danh.', 'error');
                        }

                        setTimeout(() => {
                            scanLock = false;
                        }, 1500);
                    },
                    () => {}
                );

                isCameraRunning = true;
                toggleCameraBtn.textContent = 'Tắt camera';
                showResult('Camera đã bật. Đưa mã QR sinh viên vào khung quét.', 'success');
            } catch (err) {
                console.error(err);
                reader.innerHTML = `
                    <div class="qr-placeholder">
                        Không thể mở camera để quét QR.
                        <br><br>
                        Hãy cho phép quyền camera trong trình duyệt.
                    </div>
                `;
                html5QrCode = null;
                isCameraRunning = false;
                toggleCameraBtn.textContent = 'Bật camera';
                showResult('Không thể bật camera.', 'error');
            }
        }

        async function stopScanner() {
            if (!html5QrCode || !isCameraRunning) return;

            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (err) {
                console.error(err);
            }

            reader.innerHTML = `
                <div class="qr-placeholder">
                    Camera đang tắt.
                    <br><br>
                    Nhấn "Bật camera" để quét mã QR.
                </div>
            `;

            html5QrCode = null;
            isCameraRunning = false;
            scanLock = false;
            toggleCameraBtn.textContent = 'Bật camera';
            showResult('Camera đã tắt.', 'success');
        }

        if (generateQrBtn) {
            generateQrBtn.addEventListener('click', renderEventQr);
        }

        if (toggleCameraBtn) {
            toggleCameraBtn.addEventListener('click', async function () {
                if (isCameraRunning) {
                    await stopScanner();
                } else {
                    await startScanner();
                }
            });
        }

        renderEventQr();

        if (reader) {
            reader.innerHTML = `
                <div class="qr-placeholder">
                    Camera đang tắt.
                    <br><br>
                    Nhấn "Bật camera" để quét mã QR.
                </div>
            `;
        }
    });
</script>
@endsection