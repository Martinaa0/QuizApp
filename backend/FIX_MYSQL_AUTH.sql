-- SQL komanda za rješavanje problema s autentifikacijom
-- Pokreni ovu komandu u HeidiSQL-u

-- Promijeni način autentifikacije za root korisnika
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';

-- Ako gornja komanda ne radi, probaj ovu:
-- ALTER USER 'root'@'localhost' IDENTIFIED BY '';

-- Osvježi privilegije
FLUSH PRIVILEGES;

-- Provjeri da li je promijenjeno
SELECT user, host, plugin FROM mysql.user WHERE user = 'root';
