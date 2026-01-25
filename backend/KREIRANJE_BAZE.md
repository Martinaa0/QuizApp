# Kreiranje MySQL Baze Podataka

## Problem: Authentication Method Error

Ako dobivaš grešku "authentication method unknown to the client", to znači da MySQL koristi noviji način autentifikacije.

## ✅ Rješenje 1: Kreiraj Bazu Kroz phpMyAdmin

1. Otvori `http://localhost/phpmyadmin` u browseru
2. Klikni na "New" (Nova baza) u lijevom meniju
3. Ime baze: `quiz_app`
4. Collation: `utf8mb4_unicode_ci`
5. Klikni "Create"

## ✅ Rješenje 2: Kreiraj Bazu Kroz MySQL Command Line

```bash
# Otvori MySQL command line (kroz XAMPP ili direktno)
mysql -u root -p

# Zatim u MySQL shellu:
CREATE DATABASE quiz_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

## ✅ Rješenje 3: Ažuriraj MySQL User (ako imaš problema s autentifikacijom)

```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
```

## ✅ Rješenje 4: Provjeri MySQL Server

Provjeri u XAMPP Control Panel da li je MySQL "Running" (zeleno).

## Testiranje Konekcije

Nakon kreiranja baze, testiraj:

```bash
php artisan migrate:status
```

Ili kroz tinker:
```bash
php artisan tinker
DB::connection()->getPdo();
```
