<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Report.php';
use Core\Auth;
use Models\Report;

class ReportsController {
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }
    public static function overview(): array {
        self::gate();
        return [
            'counters' => Report::counters(),
            'finance'  => Report::financeSummary(null, null),
            'instructor_load' => Report::instructorLoad(7),
            'student_progress' => Report::studentProgress(10),
        ];
    }
    public static function finance(?string $from=null, ?string $to=null): array {
        self::gate();
        return Report::financeSummary($from, $to);
    }
    public static function instructorLoad(int $days=7): array {
        self::gate();
        return Report::instructorLoad($days);
    }
}
