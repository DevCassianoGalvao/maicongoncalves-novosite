<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT . '/app/auth.php';
logout();
redirect(url('/admin/login.php'));
