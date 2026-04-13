@extends('layouts.student')

@section('student_title', 'Mã QR cá nhân')
@section('student_subtitle', 'Hiển thị, in và tải nhanh')

@section('content')
@php
$qrPayload = (string) $student->maSV;
@endphp

<style>
    .qr-page {
        min-height: calc(100vh - 160px);
        display: grid;
        place-items: center;
    }

    .qr-panel {
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 26px;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.1);
        padding: 18px;
        display: grid;
        gap: 16px;
    }

    .qr-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .qr-title {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text);
    }

    .qr-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .qr-frame {
        width: 100%;
        aspect-ratio: 1 / 1;
        padding: 18px;
        border-radius: 22px;
        background: #fff;
        border: 1px solid rgba(203, 213, 225, 0.9);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qr-art,
    .qr-art svg {
        width: 100%;
        height: auto;
        display: block;
    }

    .qr-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .qr-btn {
        appearance: none;
        min-height: 46px;
        border-radius: 14px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .qr-btn-primary {
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--primary), #2563eb);
        box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
    }

    .qr-btn-secondary {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
    }

    .qr-meta {
        padding: 14px;
        border-radius: 16px;
        background: var(--surface-soft);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .qr-meta-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-faint);
        margin-bottom: 6px;
    }

    .qr-meta-value {
        font-size: 18px;
        font-weight: 800;
        color: var(--text);
        word-break: break-word;
    }

    @media (max-width: 560px) {
        .qr-page {
            min-height: auto;
        }

        .qr-panel {
            border-radius: 20px;
            padding: 14px;
            gap: 14px;
        }

        .qr-title {
            font-size: 18px;
        }

        .qr-frame {
            padding: 14px;
            border-radius: 18px;
        }

        .qr-actions {
            grid-template-columns: 1fr;
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
        .qr-head,
        .qr-actions,
        .qr-meta {
            display: none !important;
        }

        .student-main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .student-content,
        .qr-page {
            display: block !important;
            min-height: auto !important;
        }

        .qr-panel {
            max-width: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .qr-frame {
            max-width: 360px !important;
            margin: 24px auto 0 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>

<div class="qr-page">
    <section class="qr-panel">
        <div class="qr-head">
            <div class="qr-title">Mã QR cá nhân</div>
            <div class="qr-code">{{ $student->maSV }}</div>
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
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const downloadButton = document.getElementById('downloadQrButton');
        const wrapper = document.getElementById('qr-svg-wrapper');

        if (!downloadButton || !wrapper) return;

        downloadButton.addEventListener('click', async function() {
            const svg = wrapper.querySelector('svg');
            if (!svg) return;

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