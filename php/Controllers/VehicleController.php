<?php
namespace Controllers;
require_once __DIR__ . '/../Models/Vehicle.php';

use Core\Auth;
use Core\Security;
use Models\Vehicle;

class VehicleController
{
    public static function gate(): void { Auth::requireLogin(['admin','staff']); }

    public static function save(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        $d = [
            'plate_no' => trim($post['plate_no'] ?? ''),
            'make' => trim($post['make'] ?? ''),
            'model' => trim($post['model'] ?? ''),
            'year' => ($post['year'] ?? '') !== '' ? (int)$post['year'] : null,
            'transmission' => trim($post['transmission'] ?? ''),
            'is_available' => isset($post['is_available']) ? 1 : 0,
        ];
        if ($d['plate_no'] === '') return ['error' => 'Plate number is required'];
        if ($id > 0) { Vehicle::update($id, $d); return ['ok'=>true,'id'=>$id]; }
        $newId = Vehicle::create($d); return ['ok'=>true,'id'=>$newId];
    }

    public static function delete(array $post): array {
        self::gate();
        if (!Security::verifyCsrf($post['csrf'] ?? '')) { return ['error' => 'Invalid CSRF token']; }
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) return ['error'=>'Invalid id'];
        Vehicle::delete($id);
        return ['ok'=>true];
    }
}
