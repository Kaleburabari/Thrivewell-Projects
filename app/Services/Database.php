<?php
namespace App\Services;

use PDO;

class Database
{
    public static function path(): string
    {
        return __DIR__.'/../../storage/database/thrivewell.sqlite';
    }

    public static function pdo(): PDO
    {
        $path = self::path();
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
        $pdo = new PDO('sqlite:'.$path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function fresh(): void
    {
        if (file_exists(self::path())) unlink(self::path());
    }

    public static function migrate(): void
    {
        $pdo = self::pdo();
        foreach (glob(__DIR__.'/../../database/migrations/*.php') as $migration) {
            $sql = require $migration;
            foreach ((array) $sql as $statement) $pdo->exec($statement);
        }
    }

    public static function seed(): void
    {
        require __DIR__.'/../../database/seeders/DatabaseSeeder.php';
        \DatabaseSeeder::run(self::pdo());
    }

    public static function table(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
