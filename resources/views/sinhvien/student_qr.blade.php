@extends('layouts.student')

@section('student_title', 'Mã QR cá nhân')
@section('student_subtitle', 'Hiển thị mã QR tối giản để quét, in và tải xuống thuận tiện trên mọi thiết bị')

@section('content')
@php
$qrPayload = (string) $student->maSV;
@endphp

<style>
    .qr-page {
        min-height: calc(100vh - 180px);
        display: grid;
        place-items: center;
    }

    .qr-stage {
        width: 100%;
        max-width: 720px;
        display: flex;
        justify-content: center;
    }

    .qr-panel {
        width: 100%;
        max-width: 520px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 32px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
        padding: 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 22px;
    }

    .qr-kicker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .qr-heading {
        display: grid;
        gap: 8px;
    }

    .qr-title {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text);
        line-height: 1.15;
    }

    .qr-subtitle {
        font-size: 14px;
        line-height: 1.7;
        color: var(--text-soft);
        max-width: 360px;
    }

    .qr-frame {
        width: 100%;
        max-width: 340px;
        aspect-ratio: 1 / 1;
        padding: 22px;
        border-radius: 28px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98)),
            var(--surface);
        border: 1px solid rgba(203, 213, 225, 0.9);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 18px 40px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-art {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-art svg {
        width: 100%;
        height: auto;
        display: block;
    }

    .qr-actions {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .qr-btn {
        appearance: none;
        min-height: 52px;
        border-radius: 16px;
        padding: 0 18px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .qr-btn:hover {
        transform: translateY(-1px);
    }

    .qr-btn-primary {
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.18);
    }

    .qr-btn-secondary {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    .qr-meta {
        width: 100%;
        padding: 16px 18px;
        border-radius: 18px;
        background: var(--surface-soft);
        border: 1px solid rgba(226, 232, 240, 0.9);
        display: grid;
        gap: 6px;
    }

    .qr-meta-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-faint);
    }

    .qr-meta-value {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 0.04em;
        color: var(--text);
        word-break: break-word;
    }

    .qr-note {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.7;
        max-width: 380px;
    }

    @media (max-width: 768px) {
        .qr-page {
            min-height: auto;
        }

        .qr-panel {
            max-width: 100%;
            padding: 22px;
            border-radius: 24px;
            gap: 18px;
        }

        .qr-title {
            font-size: 24px;
        }

        .qr-frame {
            max-width: 300px;
            padding: 18px;
            border-radius: 22px;
        }

        .qr-actions {
            grid-template-columns: 1fr;
        }

        .qr-meta-value {
            font-size: 18px;
        }
    }

    @media (max-width: 420px) {
        .qr-panel {
            padding: 18px;
        }

        .qr-title {
            font-size: 22px;
        }

        .qr-subtitle,
        .qr-note {
            font-size: 13px;
        }

        .qr-frame {
            max-width: 260px;
            padding: 16px;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .student-sidebar,
        .student-sidebar-backdrop,
        .student-mobile-toggle,
        .student-topbar,
        .qr-heading,
        .qr-actions,
        .qr-meta,
        .qr-note {
            display: none !important;
        }

        .student-main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .student-content,
        .qr-page,
        .qr-stage {
            display: block !important;
            min-height: auto !important;
        }

        .qr-panel {
            max-width: none !important;
            width: auto !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .qr-kicker {
            display: none !important;
        }

        .qr-frame {
            max-width: 360px !important;
            margin: 24px auto 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #fff !important;
            padding: 0 !important;
        }
    }
</style>

<div class="qr-page">
    <div class="qr-stage">
        <section class="qr-panel">
            <div class="qr-kicker">Mã QR cá nhân</div>

            <div class="qr-heading">
                <h2 class="qr-title">Quét nhanh, hiển thị rõ</h2>
                <p class="qr-subtitle">
                    Trang này chỉ tập trung vào mã QR để quét, in và tải xuống nhanh trên điện thoại, tablet và desktop.
                </p>
            </div>

            <div class="qr-frame" id="qr-download-target" aria-label="Mã QR sinh viên {{ $student->maSV }}">
                <div class="qr-art" id="qr-svg-wrapper">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(320)->margin(1)->generate($qrPayload) !!}
                </div>
            </div>

            <div class="qr-actions">
                <button type="button" class="qr-btn qr-btn-primary" onclick="window.print()">In mã QR</button>
                <button type="button" class="qr-btn qr-btn-secondary" id="downloadQrButton">Tải mã QR</button>
            </div>

            <div class="qr-meta">
                <div class="qr-meta-label">Mã sinh viên</div>
                <div class="qr-meta-value">{{ $student->maSV }}</div>
            </div>

            <p class="qr-note">
                Nội dung mã QR chỉ chứa mã sinh viên để đồng bộ đúng với luồng quét hiện tại của hệ thống.
            </p>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const downloadButton = document.getElementById('downloadQrButton');
        const wrapper = document.getElementById('qr-svg-wrapper');

        if (!downloadButton || !wrapper) {
            return;
        }

        downloadButton.addEventListener('click', async function() {
            const svg = wrapper.querySelector('svg');

            if (!svg) {
                return;
            }

            const clonedSvg = svg.cloneNode(true);
            const size = 1200;
            clonedSvg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            clonedSvg.setAttribute('width', size);
            clonedSvg.setAttribute('height', size);
            clonedSvg.setAttribute('viewBox', svg.getAttribute('viewBox') || `0 0 ${size} ${size}`);

            const svgData = new XMLSerializer().serializeToString(clonedSvg);
            const svgBlob = new Blob([svgData], {
                type: 'image/svg+xml;charset=utf-8'
            });
            const svgUrl = URL.createObjectURL(svgBlob);
            const image = new Image();

            image.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;

                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                URL.revokeObjectURL(svgUrl);

                const pngUrl = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = pngUrl;
                link.download = 'qr-{{ $student->maSV }}.png';
                document.body.appendChild(link);
                link.click();
                link.remove();
            };

            image.onerror = function() {
                URL.revokeObjectURL(svgUrl);

                const fallbackLink = document.createElement('a');
                fallbackLink.href = svgUrl;
                fallbackLink.download = 'qr-{{ $student->maSV }}.svg';
                document.body.appendChild(fallbackLink);
                fallbackLink.click();
                fallbackLink.remove();
            };

            image.src = svgUrl;
        });
    });
</script>
@endsection