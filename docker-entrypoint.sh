#!/usr/bin/env bash
set -e

echo "Menunggu koneksi database MySQL..."
until php -r '
$host = getenv("database.default.hostname") ?: "db";
$user = getenv("database.default.username") ?: "prorata";
$pass = getenv("database.default.password") ?: "proratapass";
$db   = getenv("database.default.database") ?: "prorataforge";
$port = (int)(getenv("database.default.port") ?: 3306);
$conn = @new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    exit(1);
}
$conn->close();
exit(0);
' 2>/dev/null; do
    echo "Database belum siap, mencoba lagi dalam 2 detik..."
    sleep 2
done

echo "Database terhubung! Menjalankan migrasi database..."
php spark migrate --all --force
php spark db:seed BillingSeeder --force 2>/dev/null || true

echo "Memulai server ProrataForge di http://0.0.0.0:8080..."
exec php spark serve --host 0.0.0.0 --port 8080
