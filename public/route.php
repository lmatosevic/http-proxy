<?php

// route.php
if (preg_match('/\.(?:html|pdf|txt)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    include __DIR__ . '/index.php';
}