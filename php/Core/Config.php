<?php
namespace Core;

require_once __DIR__ . '/SecurityHeaders.php';
// Basic config — edit to match your local DB
class Config {
    public const DB_HOST = '127.0.0.1';
    public const DB_NAME = 'origin_driving';
    public const DB_USER = 'root';
    public const DB_PASS = '';
    public const DB_CHARSET = 'utf8mb4';
}
