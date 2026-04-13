@extends('layouts.student')

@section('student_title', 'Quét Mã Điểm Danh')
@section('student_subtitle', 'Dùng camera để quét QR sự kiện và ghi nhận điểm danh')

@section('content')
    <style>
        .scan-shell {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .scan-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 20px;
        }

        .scan-card,
        .scan-history-wrap,
        .scan-info-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
        }

        .scan-card,
        .scan-info-card {
            padding: 22px;
        }

        .scan-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .scan-subtitle {
            font-size: 13px;
            color: var(--text-soft);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .scan-reader-box {
            min-height: 340px;
            border: 1px dashed var(--border-strong);
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

        .scan-result {
            margin-top: 16px;
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

        .manual-box {
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .manual-box textarea {
            width: 100%;
            min-height: 110px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            font-size: 13px;
            resize: vertical;
            outline: none;
        }

        .manual-box textarea:focus {
            border-color: rgba(29, 78, 216, 0.35);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.08);
        }

        .scan-action-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .scan-btn,
        .scan-btn-secondary {
            border: none;
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .scan-btn {
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: #fff;
            box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
        }

        .scan-btn-secondary {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text-soft);
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

        .student-quick-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .student-quick-item {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--border);
            background: linear-gradient(180deg, #ffffff, #fbfdff);
        }

        .student-quick-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-faint);
            margin-bottom: 6px;
        }

        .student-quick-value {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.45;
            word-break: break-word;
        }

        .scan-history-wrap {
            overflow: hidden;
        }

        .scan-history-header {
            padding: 20px 22px;
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .scan-history-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }

        .scan-history-subtitle {
            font-size: 13px;
            color: var(--text-soft);
        }

        .scan-history-table-wrap {
            overflow-x: auto;
        }

        .scan-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .scan-history-table thead th {
            padding: 14px 16px;
            background: var(--surface-soft);
            color: var(--text);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .scan-history-table tbody td {
            padding: 16px;
            font-size: 14px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .scan-history-table tbody tr:last-child td {
            border-bottom: none;
        }

        .scan-history-table tbody tr:hover {
            background: #fafcff;
        }

        .history-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: #ecfdf3;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .empty-box {
            padding: 28px 20px;
            text-align: center;
            color: var(--text-soft);
            font-size: 14px;
        }

        .qr-placeholder {
            font-size: 13px;
            color: var(--text-soft);
            text-align: center;
            max-width: 240px;
            line-height: 1.6;
        }

        .camera-off {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 240px;
            text-align: center;
        }

        .camera-note {
            margin-top: 10px;
            font-size: 12px;
            color: var(--text-soft);
        }

        @media (max-width: 1100px) {
            .scan-grid {
                grid-template-columns: 1fr;
            }

            .student-quick-info {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content-header">
        <div>
            <div class="page-heading">Quét Mã Điểm Danh</div>
            <div class="section-subtitle">
                Dùng camera để quét QR sự kiện. Hệ thống sẽ tự ghi nhận điểm danh cho tài khoản của bạn.
            </div>
        </div>
    </div>

    <div class="scan-shell">
        <div class="scan-grid">
            <div class="scan-card">
                <div class="scan-title">Camera quét QR sự kiện</div>
                <div class="scan-subtitle">
                    Đưa mã QR sự kiện vào khung quét. Sau khi quét thành công, hệ thống sẽ kiểm tra sự kiện và lưu điểm danh.
                </div>

                <div class="scan-reader-box">
                    <div id="student-qr-reader">
                        <div class="qr-placeholder camera-off">
                            Nhấn <strong>Bật camera</strong> để bắt đầu quét mã QR sự kiện.
                        </div>
                    </div>
                </div>

                <div class="scan-action-row" style="margin-top: 14px;">
                    <button type="button" class="scan-btn" id="toggle-camera-btn">Bật camera</button>
                </div>

                <div class="camera-note">
                    Bạn có thể bật hoặc tắt camera bất cứ lúc nào trong quá trình điểm danh.
                </div>

                <div id="student-scan-result" class="scan-result"></div>

                <div class="manual-box">
                    <div class="scan-subtitle" style="margin-bottom:0;">
                        Nếu máy không mở được camera, bạn có thể nhập mã hoạt động để điểm danh thủ công.
                    </div>

                    <textarea id="manual-qr-input" placeholder="Ví dụ: 1 hoặc 15 hoặc 28"></textarea>

                    <div class="scan-action-row">
                        <button type="button" class="scan-btn" id="manual-submit-btn">Xác nhận điểm danh</button>
                        <button type="button" class="scan-btn-secondary" id="manual-clear-btn">Xóa nội dung</button>
                    </div>
                </div>
            </div>

            <div class="scan-info-card">
                <div class="scan-title">Thông tin sinh viên</div>
                <div class="scan-subtitle">
                    Điểm danh sẽ được ghi nhận cho chính tài khoản đang đăng nhập.
                </div>

                <div class="student-quick-info">
                    <div class="student-quick-item">
                        <div class="student-quick-label">Họ và tên</div>
                        <div class="student-quick-value">{{ $student->hoTen }}</div>
                    </div>

                    <div class="student-quick-item">
                        <div class="student-quick-label">Mã sinh viên</div>
                        <div class="student-quick-value">{{ $student->maSV }}</div>
                    </div>

                    <div class="student-quick-item">
                        <div class="student-quick-label">Lớp</div>
                        <div class="student-quick-value">{{ $student->maLop ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="student-quick-item">
                        <div class="student-quick-label">Trạng thái</div>
                        <div class="student-quick-value">{{ $student->trangThai ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scan-history-wrap">
            <div class="scan-history-header">
                <div class="scan-history-title">Lịch sử điểm danh gần đây</div>
                <div class="scan-history-subtitle">10 lượt điểm danh mới nhất của bạn</div>
            </div>

            @if (!empty($recentAttendances) && count($recentAttendances) > 0)
                <div class="scan-history-table-wrap" id="attendance-history-table-wrap">
                    <table class="scan-history-table">
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
                                    <td>{{ $item['event_name'] }}</td>
                                    <td>{{ $item['date'] }}</td>
                                    <td>{{ $item['time'] }}</td>
                                    <td><span class="history-status">{{ $item['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-box" id="attendance-history-empty">
                    Bạn chưa có dữ liệu điểm danh nào.
                </div>

                <div class="scan-history-table-wrap" style="display:none;" id="attendance-history-table-wrap">
                    <table class="scan-history-table">
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
        </div>
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
                    <div class="qr-placeholder camera-off">
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
                        <td>${attendance.event_name}</td>
                        <td>${attendance.date}</td>
                        <td>${attendance.time}</td>
                        <td><span class="history-status">${attendance.status}</span></td>
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
                        body: JSON.stringify({
                            qr_payload: qrPayload,
                        }),
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
                    showResult('Vui lòng nhập mã hoạt động để kiểm tra.', 'error');
                    return;
                }

                submitAttendance(value);
            });

            manualClearBtn.addEventListener('click', function () {
                manualInput.value = '';
            });

            if (typeof Html5Qrcode === 'undefined') {
                readerElement.innerHTML = `
                    <div class="qr-placeholder">
                        Không tải được thư viện quét QR.
                        <br><br>
                        Kiểm tra kết nối mạng hoặc CDN.
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

                            setTimeout(() => {
                                scanLock = false;
                            }, 1500);
                        },
                        () => {}
                    );

                    scannerRunning = true;
                    showResult('Camera đã được bật. Đưa QR sự kiện vào khung quét.', 'success');
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
                if (!scannerRunning || !html5QrCode || scannerBusy) {
                    return;
                }

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