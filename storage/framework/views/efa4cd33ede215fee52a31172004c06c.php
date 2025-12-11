<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Bingkis Kaca')); ?> - <?php echo $__env->yieldContent('title', 'Photo Booth'); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo e(route('home')); ?>">
                    <img src="<?php echo e(asset('images/logo_bingkis_kaca.png')); ?>" alt="Bingkis Kaca Logo" class="brand-logo">
                </a>
            </div>

            <div class="nav-menu">
                <a href="<?php echo e(route('home')); ?>" class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">Home</a>
                <a href="<?php echo e(route('photobooth')); ?>" class="nav-link <?php echo e(request()->routeIs('photobooth') ? 'active' : ''); ?>">PhotoBooth</a>
                <a href="<?php echo e(route('help')); ?>" class="nav-link <?php echo e(request()->routeIs('help') ? 'active' : ''); ?>">Help</a>
                
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('profile.index')); ?>" class="nav-link <?php echo e(request()->routeIs('profile.*') ? 'active' : ''); ?>">Profile</a>
                    
                    <?php if(auth()->user()->isAdmin()): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.*') ? 'active' : ''); ?>">Admin</a>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="nav-link-btn">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="nav-link">Login</a>
                    <a href="<?php echo e(route('register')); ?>" class="nav-link">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo e(date('Y')); ?> Bingkis Kaca. All rights reserved.</p>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\bingkis-kaca\resources\views/layouts/app.blade.php ENDPATH**/ ?>