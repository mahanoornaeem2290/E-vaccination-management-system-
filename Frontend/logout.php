<?php
require __DIR__ . '/../Backend/config/config.php';
$_SESSION = [];
session_destroy();
session_start();
flash('You have been logged out.');
redirect('login.php');
