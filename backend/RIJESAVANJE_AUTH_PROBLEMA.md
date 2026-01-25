# Rješavanje MySQL Autentifikacije - Detaljne Instrukcije

## Problem
Greška: `auth_gssapi_client` - MySQL koristi noviji način autentifikacije koji PHP PDO ne podržava.

## ✅ Rješenje 1: Promijeni Autentifikaciju u HeidiSQL (PREPORUČENO)

### Korak po korak:

1. **Otvori HeidiSQL**
2. **Spoji se na MySQL server** (ako već nisi spojen)
3. **Klikni na Query tab** (ili pritisni `F9`)
4. **Zalijepi ovu komandu:**

```sql
-- Provjeri trenutni način autentifikacije
SELECT user, host, plugin FROM mysql.user WHERE user = 'root';

-- Promijeni način autentifikacije
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';

-- Ako imaš i 'root'@'127.0.0.1', promijeni i to:
ALTER USER 'root'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY '';

-- Osvježi privilegije
FLUSH PRIVILEGES;

-- Provjeri da li je promijenjeno
SELECT user, host, plugin FROM mysql.user WHERE user = 'root';
```

5. **Pokreni komandu** (F9 ili gumb "Execute")
6. **Zatvori i ponovno otvori HeidiSQL konekciju**
7. **Testiraj u Laravel-u:**

```bash
php artisan migrate:status
```

---

## ✅ Rješenje 2: Ako koristiš MariaDB

Ako koristiš MariaDB umjesto MySQL-a, komanda je malo drugačija:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('');
FLUSH PRIVILEGES;
```

---

## ✅ Rješenje 3: Kreiraj Novog Korisnika

Ako gornje ne radi, kreiraj novog korisnika:

```sql
-- Kreiraj novog korisnika
CREATE USER 'quiz_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password123';

-- Daj mu sve privilegije na quiz_app bazu
GRANT ALL PRIVILEGES ON quiz_app.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
```

Zatim promijeni u `.env`:
```
DB_USERNAME=quiz_user
DB_PASSWORD=password123
```

---

## ✅ Rješenje 4: Provjeri MySQL Verziju

U HeidiSQL-u pokreni:
```sql
SELECT VERSION();
```

Ako je verzija 8.0+, problem je s caching_sha2_password pluginom.

---

## ✅ Rješenje 5: Privremeno Koristi SQLite

Ako ništa ne radi, možeš privremeno koristiti SQLite dok ne riješiš MySQL problem:

U `.env` promijeni:
```
DB_CONNECTION=sqlite
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=quiz_app
# DB_USERNAME=root
# DB_PASSWORD=
```

SQLite fajl će se automatski kreirati u `database/database.sqlite`.

---

## 🔍 Provjera

Nakon svakog rješenja, testiraj:

```bash
php artisan migrate:status
```

Ili kroz tinker:
```bash
php artisan tinker
DB::connection()->getPdo();
```
