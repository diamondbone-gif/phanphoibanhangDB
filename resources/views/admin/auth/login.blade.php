<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Lý Vận Hành</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- CSS riêng -->
    <link rel="stylesheet" href="{{ asset('admin/css/login.css') }}">
</head>

<body class="text-gray-900 antialiased min-h-screen relative flex items-center justify-center p-4 sm:p-8">

    <!-- Các khối trang trí nền -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Container chính -->
    <div
        class="relative z-10 flex w-full max-w-[900px] bg-white rounded-2xl md:rounded-[2rem] shadow-[0_25px_50px_-12px_rgba(110,60,30,0.3)] overflow-hidden min-h-[auto] md:min-h-[550px]">

        <!-- Nửa trái: Form đăng nhập -->
        <div class="w-full md:w-1/2 flex flex-col justify-center px-6 py-10 sm:px-12 md:px-10 lg:px-16">

            <div class="mb-8 md:mb-10 text-center">
                <h1 class="text-3xl md:text-4xl font-extrabold text-[#7a4123]">
                    Đăng Nhập
                </h1>

                <p class="mt-2 text-sm text-[#a48677] font-semibold">
                    Quản lý vận hành
                </p>
            </div>

            <!-- Form Laravel thật -->
            <form id="loginForm" method="POST" action="{{ route('admin.login.submit') }}"
                class="space-y-5 md:space-y-6">
                @csrf

                <!-- Gmail đăng nhập -->
                <div>
                    <label for="email" class="block text-[13px] font-bold text-[#a48677] mb-1.5 md:mb-2 pl-1">
                        Gmail đăng nhập
                    </label>

                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="block w-full rounded-xl bg-[#f4ebe5] px-4 py-3 md:px-5 md:py-3.5 text-[#7a4123] focus:outline-none focus:ring-2 focus:ring-[#c27546] transition-all text-sm md:text-base"
                        placeholder="abc@gmail.com">

                    @error('email')
                    <div class="login-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label for="password" class="block text-[13px] font-bold text-[#a48677] mb-1.5 md:mb-2 pl-1">
                        Mật khẩu
                    </label>

                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="block w-full rounded-xl bg-[#f4ebe5] px-4 py-3 md:px-5 md:py-3.5 pr-12 text-[#7a4123] focus:outline-none focus:ring-2 focus:ring-[#c27546] transition-all text-sm md:text-base"
                            placeholder="Nhập mật khẩu của bạn">

                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#a48677] hover:text-[#7a4123] transition-colors">
                            <i class="ph-bold ph-eye text-lg md:text-xl" id="eyeIcon"></i>
                        </button>
                    </div>

                    @error('password')
                    <div class="login-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ghi nhớ đăng nhập -->
                <label class="flex items-center gap-2 text-sm font-semibold text-[#a48677]">
                    <input type="checkbox" name="remember" value="1"
                        class="rounded border-[#c27546] text-[#bf6e3f] focus:ring-[#bf6e3f]">
                    Ghi nhớ đăng nhập
                </label>

                <!-- Nút đăng nhập -->
                <button type="submit" id="submitButton"
                    class="w-full flex justify-center items-center py-3 md:py-3.5 px-4 border border-transparent rounded-xl shadow-[0_8px_20px_rgba(194,117,70,0.3)] text-sm md:text-[15px] font-bold text-white bg-[#bf6e3f] hover:bg-[#a65d32] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#bf6e3f] transition-all transform hover:-translate-y-0.5 mt-4 md:mt-6">
                    <span id="btnText">Đăng nhập</span>
                    <i class="ph-bold ph-spinner animate-spin ml-2 text-base md:text-lg hidden" id="btnIcon"></i>
                </button>
            </form>
        </div>

        <!-- Nửa phải: Hình ảnh -->
        <div class="hidden md:block md:w-1/2 relative bg-[#eae0d9]">
            <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1919&q=80"
                alt="Không gian làm việc"
                class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-multiply">

            <div class="absolute inset-0 bg-gradient-to-b from-white/30 to-transparent"></div>
        </div>
    </div>

    <!-- JS riêng -->
    <script src="{{ asset('admin/js/login.js') }}"></script>
</body>

</html>