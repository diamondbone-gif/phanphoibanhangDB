<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Chào mừng trở lại</title>

    <!-- Tích hợp Tailwind CSS cho giao diện -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tích hợp Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        /* Tùy chỉnh font chữ cho giống thiết kế hiện đại */

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Ẩn thanh cuộn */

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-white text-gray-900 antialiased">

    <!-- Bố cục chia đôi màn hình -->
    <div class="flex min-h-screen">

        <!-- NỬA TRÁI: Form Đăng nhập -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 lg:px-24 xl:px-32 relative">
            <div class="max-w-md w-full mx-auto">

                <!-- Tiêu đề -->
                <div class="mb-8 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-2">Chào mừng trở lại</h1>
                    <p class="text-sm text-gray-500 font-medium">Chào mừng trở lại! Vui lòng nhập thông tin của bạn.</p>
                </div>

                <!-- Form -->
                <form id="loginForm" class="space-y-5">

                    <!-- Nhập Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" id="email" required
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 transition-colors sm:text-sm"
                            placeholder="Nhập email của bạn">
                    </div>

                    <!-- Nhập Mật khẩu -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Mật khẩu</label>
                        <div class="relative">
                            <input type="password" id="password" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 transition-colors sm:text-sm"
                                placeholder="••••••••">
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="ph ph-eye text-lg" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Ghi nhớ -->
                    <div class="flex items-center py-1">
                        <input type="checkbox" id="remember"
                            class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm font-medium text-gray-600 cursor-pointer">
                            Ghi nhớ trong 30 ngày
                        </label>
                    </div>

                    <!-- Nút Đăng nhập -->
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#101828] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all">
                        <span id="btnText">Đăng nhập</span>
                        <i class="ph-bold ph-spinner animate-spin ml-2 text-lg hidden" id="btnIcon"></i>
                    </button>
                </form>

            </div>
        </div>

        <!-- NỬA PHẢI: Hình ảnh và Trích dẫn (Ẩn trên Mobile) -->
        <div class="hidden lg:block lg:w-1/2 relative bg-gray-100 overflow-hidden">
            <!-- Hình ảnh nền thay thế (có thể đổi link ảnh sau) -->
            <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80"
                alt="Office Background" class="absolute inset-0 w-full h-full object-cover object-top" />

            <!-- Overlay làm tối nhẹ ảnh -->
            <div class="absolute inset-0 bg-gray-900/10"></div>

            <!-- Block Trích dẫn (Glassmorphism) -->
            <div
                class="absolute bottom-10 left-10 right-10 p-8 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white shadow-2xl">

                <!-- Đánh giá sao -->
                <div class="flex space-x-1 mb-4 text-white">
                    <i class="ph-fill ph-star text-lg"></i>
                    <i class="ph-fill ph-star text-lg"></i>
                    <i class="ph-fill ph-star text-lg"></i>
                    <i class="ph-fill ph-star text-lg"></i>
                    <i class="ph-fill ph-star text-lg"></i>
                </div>

                <p class="text-2xl font-medium leading-tight mb-8">
                    "Chúng tôi đã sử dụng nền tảng này để khởi động mọi dự án mới và không thể tưởng tượng được việc làm
                    việc thiếu nó."
                </p>

                <div class="flex justify-between items-end">
                    <!-- Thông tin tác giả -->
                    <div>
                        <h4 class="font-bold text-lg">Andi Lane</h4>
                        <p class="text-sm font-medium text-white/80">Người sáng lập, Catalog</p>
                        <p class="text-xs text-white/70 mt-0.5">Web Design Agency</p>
                    </div>

                    <!-- Nút điều hướng mũi tên -->
                    <div class="flex space-x-3">
                        <button
                            class="w-10 h-10 rounded-full border border-white/50 flex items-center justify-center hover:bg-white/20 transition-colors backdrop-blur-sm">
                            <i class="ph border-white ph-arrow-left text-lg"></i>
                        </button>
                        <button
                            class="w-10 h-10 rounded-full border border-white/50 flex items-center justify-center hover:bg-white/20 transition-colors backdrop-blur-sm">
                            <i class="ph border-white ph-arrow-right text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- JS Logic cơ bản cho Form -->
    <script>
        // Ẩn/hiện mật khẩu
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            if (type === 'text') {
                eyeIcon.classList.replace('ph-eye', 'ph-eye-slash');
            } else {
                eyeIcon.classList.replace('ph-eye-slash', 'ph-eye');
            }
        });

        // Xử lý Form Đăng nhập giả lập
        const loginForm = document.getElementById('loginForm');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Hiệu ứng Loading
            btnText.textContent = 'Đang xử lý...';
            btnIcon.classList.remove('hidden');

            // Giả lập xử lý 1.5 giây
            setTimeout(() => {
                btnText.textContent = 'Đăng nhập';
                btnIcon.classList.add('hidden');
                alert('Đăng nhập thành công!');
                loginForm.reset();
            }, 1500);
        });
    </script>
</body>

</html>