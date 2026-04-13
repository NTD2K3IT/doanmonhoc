<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .btn {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 12px;
            background: #1d4ed8;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .error-box {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="title">Đăng nhập</div>
        <div class="subtitle">Hệ thống quản lý điểm danh CTXH</div>

        @if ($errors->has('login'))
            <div class="error-box">
                {{ $errors->first('login') }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}">
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control">
            </div>

            <button type="submit" class="btn">Đăng nhập</button>
        </form>
    </div>
</body>
</html>