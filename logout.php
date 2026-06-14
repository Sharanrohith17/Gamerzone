<?php
require_once __DIR__ . '/../includes/config.php';

logout_all();
set_flash('success', 'Admin session ended.');
redirect('admin/login.php');
