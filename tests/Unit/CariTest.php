<?php

namespace Tests\Unit;

use App\Support\Cari;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CariTest extends TestCase
{
    public static function driver(): array
    {
        return [
            'PostgreSQL' => [PostgresConnection::class, 'pgsql', 'ILIKE'],
            'SQLite' => [SQLiteConnection::class, 'sqlite', 'LIKE'],
        ];
    }

    #[DataProvider('driver')]
    public function test_sql_memakai_escape_eksplisit_dan_binding_tanpa_koneksi_database(string $class, string $driver, string $operator): void
    {
        $koneksi = new $class(null, 'testing', '', ['driver' => $driver]);
        $query = $koneksi->query()->from('users')->where('status', 'aktif');

        Cari::mengandung($query, 'users.name', "O'Reilly%_\\", 'or');

        $this->assertSame(
            'select * from "users" where "status" = ? or "users"."name" '.$operator.' ? ESCAPE ?',
            $query->toSql(),
        );
        $this->assertSame(['aktif', "%O'Reilly\\%\\_\\\\%", '\\'], $query->getBindings());
    }
}
