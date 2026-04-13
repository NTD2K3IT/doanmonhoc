@extends('layouts.student')

@section('student_title', 'Quét mã điểm danh')
@section('student_subtitle', 'Quét QR sự kiện để ghi nhận điểm danh')

@section('content')
<style>
    .scan-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .scan-card,
    .scan-history-wrap,
    .scan-fallback {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
    }

    .scan-card,
    .scan-fallback {
        padding: 22px;
    }

    .scan-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .scan-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text);
        margin-bottom: 6px;
    }

    .scan-subtitle {
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-soft);
        max-width: 680px;
    }

    .scan-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .scan-reader-box {
        min-height: 360px;
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        background: var(--surface-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    #student-qr-reader {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
    }

    .scan-placeholder {
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.7;
        max-width: 260px;
    }

    .scan-actions {
        margin-top: 16px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .scan-btn,
    .scan-btn-secondary {
        appearance: none;
        border-radius: 12px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .scan-btn {
        border: none;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        color: #fff;
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
    }

    .scan-btn-secondary {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
    }

    .scan-btn:hover,
    .scan-btn-secondary:hover {
        transform: translateY(-1px);
    }

    .scan-btn:disabled,
    .scan-btn-secondary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .scan-result {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.6;
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

    .fallback-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 6px;
    }

    .fallback-subtitle {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .manual-input {
        width: 100%;
        min-height: 96px;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 14px;
        font-size: 14px;
        resize: vertical;
        outline: none;
    }

    .manual-input:focus {
        border-color: rgba(29, 78, 216, 0.35);
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.08);
    }

    .scan-history-wrap {
        overflow: hidden;
    }

    .history-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .history-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
    }

    .history-subtitle {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.6;
    }

    .history-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-table-wrap {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
    }

    .history-table thead th {
        padding: 14px 16px;
        background: var(--surface-soft);
        border-bottom: 1px solid var(--border);
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text);
        white-space: nowrap;
    }

    .history-table tbody td {
        padding: 16px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
        color: var(--text);
        vertical-align: middle;
    }

    .history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .history-table tbody tr:hover {
        background: #fafcff;
    }

    .history-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .empty-box {
        padding: 28px 22px;
        text-align: center;
        color: var(--text-soft);
        font-size: 14px;
        line-height: 1.7;
    }

    @media (max-width: 768px) {
        .scan-card,
        .scan-fallback {
            padding: 18px;
        }

        .scan-head,
        .history-header {
            flex-direction: column;
            align-items: stretch;
        }

        .scan-title {
            font-size: 20px;
        }

        .scan-actions {
            flex-direction: column;
        }

        .scan-btn,
        .scan-btn-secondary {
            width: 100%;
        }

        .history-table-wrap {
            overflow: visible;
        }

        .history-table,
        .history-table thead,
        .history-table tbody,
        .history-table th,
        .history-table td,
        .history-table tr {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .history-table thead {
            display: none;
        }

        .history-table tbody {
            padding: 10px;
        }

        .history-table tbody tr {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .history-table tbody td {
            border-bottom: 1px solid var(--border);
            padding: 12px 14px;
        }

        .history-table tbody td:last-child {
            border-bottom: none;
        }

        .history-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-faint);
        }
    }
</style>

<div class="scan-page">
    <section class="scan-card">
        <div class="scan-head">
            <div>
                <div class="scan-title">Quét mã QR sự kiện</div>
                <div class="scan-subtitle">
                    Mở camera để quét nhanh mã QR của sự kiện. Hệ thống sẽ tự động ghi nhận điểm danh cho tài khoản đang đăng nhập.
                </div>
            </div>
            <div class="scan-badge">{{ $student->maSV }}</div>
        </div>

        <div class="scan-reader-box">
            <div id="student-qr-reader">
                <div class="scan-placeholder">
                    Nhấn <strong>Bật camera</strong> để bắt đầu quét mã QR sự kiện.
                </div>
            </div>
        </div>

        <div class="scan-actions">
            <button type="button" class="scan-btn" id="toggle-camera-btn">Bật camera</button>
        </div>

        <div id="student-scan-result" class="scan-result"></div>
    </section>

    <section class="scan-fallback">
        <div class="fallback-title">Nhập mã thủ công</div>
        <div class="fallback-subtitle">Dùng khi thiết bị không mở được camera hoặc mã QR khó quét.</div>

        <textarea id="manual-qr-input" class="manual-input" placeholder="Ví dụ: 1 hoặc 15 hoặc 28"></textarea>

        <div class="scan-actions">
            <button type="button" class="scan-btn" id="manual-submit-btn">Xác nhận điểm danh</button>
            <button type="button" class="scan-btn-secondary" id="manual-clear-btn">Xóa nội dung</button>
        </div>
    </section>

    <section class="scan-history-wrap">
        <div class="history-header">
            <div>
                <div class="history-title">Lịch sử gần đây</div>
                <div class="history-subtitle">10 lượt điểm danh gần nhất được ghi nhận trên hệ thống.</div>
            </div>
            <div class="history-counter">{{ count($recentAttendances) }}</div>
        </div>

        @if (!empty($recentAttendances) && count($recentAttendances) > 0)
            <div class="history-table-wrap" id="attendance-history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Sự kiện</th>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-history-body">
                        @foreach ($recentAttendances as $item)
                            <tr>
                                <td data-label="Sự kiện">{{ $item['event_name'] }}</td>
                                <td data-label="Ngày">{{ $item['date'] }}</td>
                                <td data-label="Giờ">{{ $item['time'] }}</td>
                                <td data-label="Trạng thái"><span class="history-status">{{ $item['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-box" id="attendance-history-empty">
                Chưa có dữ liệu điểm danh gần đây.
            </div>

            <div class="history-table-wrap" style="display:none;" id="attendance-history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Sự kiện</th>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-history-body"></tbody>
                </table>
            </div>
        @endif
    </section>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resultBox = document.getElementById('student-scan-result');
        const manualInput = document.getElementById('manual-qr-input');
        const manualSubmitBtn = document.getElementById('manual-submit-btn');
        const manualClearBtn = document.getElementById('manual-clear-btn');
        const historyBody = document.getElementById('attendance-history-body');
        const historyEmpty = document.getElementById('attendance-history-empty');
        const historyTableWrap = document.getElementById('attendance-history-table-wrap');
        const checkInUrl = '{{ route('sinhvien.scan_qr.check_in') }}';
        const csrfToken = '{{ csrf_token() }}';
        const readerElement = document.getElementById('student-qr-reader');
        const toggleCameraBtn = document.getElementById('toggle-camera-btn');

        let html5QrCode = null;
        let scanLock = false;
        let scannerRunning = false;
        let scannerBusy = false;

        function showResult(message, type = 'success') {
            resultBox.className = 'scan-result ' + type;
            resultBox.textContent = message;
        }

        function renderCameraOffState() {
            readerElement.innerHTML = `
                <div class="scan-placeholder">
                    Nhấn <strong>Bật camera</strong> để bắt đầu quét mã QR sự kiện.
                </div>
            `;
        }

        function setToggleButtonState() {
            if (scannerRunning) {
                toggleCameraBtn.textContent = 'Tắt camera';
                toggleCameraBtn.classList.remove('scan-btn');
                toggleCameraBtn.classList.add('scan-btn-secondary');
            } else {
                toggleCameraBtn.textContent = 'Bật camera';
                toggleCameraBtn.classList.remove('scan-btn-secondary');
                toggleCameraBtn.classList.add('scan-btn');
            }

            toggleCameraBtn.disabled = scannerBusy;
        }

        function prependHistoryRow(attendance) {
            if (!attendance || !historyBody) return;

            const rowHtml = `
                <tr>
                    <td data-label="Sự kiện">${attendance.event_name}</td>
                    <td data-label="Ngày">${attendance.date}</td>
                    <td data-label="Giờ">${attendance.time}</td>
                    <td data-label="Trạng thái"><span class="history-status">${attendance.status}</span></td>
                </tr>
            `;

            if (historyEmpty) {
                historyEmpty.remove();
            }

            if (historyTableWrap) {
                historyTableWrap.style.display = 'block';
            }

            historyBody.insertAdjacentHTML('afterbegin', rowHtml);

            while (historyBody.children.length > 10) {
                historyBody.removeChild(historyBody.lastElementChild);
            }
        }

        async function submitAttendance(qrPayload) {
            try {
                const response = await fetch(checkInUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qr_payload: qrPayload }),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    showResult(result.message || 'Không thể điểm danh.', 'error');
                    return;
                }

                showResult(result.message, 'success');

                if (result.attendance) {
                    prependHistoryRow(result.attendance);
                }
            } catch (error) {
                showResult('Lỗi kết nối khi gửi điểm danh.', 'error');
            }
        }

        manualSubmitBtn.addEventListener('click', function () {
            const value = manualInput.value.trim();

            if (!value) {
                showResult('Vui lòng nhập mã hoạt động.', 'error');
                return;
            }

            submitAttendance(value);
        });

        manualClearBtn.addEventListener('click', function () {
            manualInput.value = '';
        });

        if (typeof Html5Qrcode === 'undefined') {
            readerElement.innerHTML = `
                <div class="scan-placeholder">
                    Không tải được thư viện quét QR. Vui lòng kiểm tra kết nối mạng.
                </div>
            `;
            toggleCameraBtn.disabled = true;
            return;
        }

        async function startScanner() {
            if (scannerRunning || scannerBusy) return;

            scannerBusy = true;
            setToggleButtonState();

            try {
                readerElement.innerHTML = '';
                html5QrCode = new Html5Qrcode('student-qr-reader');

                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: 240 },
                    async (decodedText) => {
                        if (scanLock) return;
                        scanLock = true;
                        await submitAttendance(decodedText);
                        setTimeout(() => { scanLock = false; }, 1500);
                    },
                    () => {}
                );

                scannerRunning = true;
                showResult('Camera đã sẵn sàng. Đưa mã QR vào khung quét.', 'success');
            } catch (error) {
                renderCameraOffState();
                showResult('Không thể mở camera. Hãy cho phép quyền camera trong trình duyệt.', 'error');
                html5QrCode = null;
                scannerRunning = false;
            }

            scannerBusy = false;
            setToggleButtonState();
        }

        async function stopScanner() {
            if (!scannerRunning || !html5QrCode || scannerBusy) return;

            scannerBusy = true;
            setToggleButtonState();

            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (error) {
                console.error(error);
            }

            html5QrCode = null;
            scannerRunning = false;
            renderCameraOffState();
            showResult('Camera đã được tắt.', 'success');

            scannerBusy = false;
            setToggleButtonState();
        }

        toggleCameraBtn.addEventListener('click', async function () {
            if (scannerRunning) {
                await stopScanner();
            } else {
                await startScanner();
            }
        });

        renderCameraOffState();
        setToggleButtonState();
    });
</script>
@endsection
