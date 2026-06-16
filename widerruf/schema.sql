-- Schema für die Widerruf-Funktion (§ 356a BGB)
-- Ausführen mit:  mysql -u root -p < schema.sql
-- oder den CREATE-TABLE-Teil in der bestehenden DB ausführen.

CREATE DATABASE IF NOT EXISTS widerruf
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE widerruf;

CREATE TABLE IF NOT EXISTS widerrufe (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    referenz     VARCHAR(32)     NOT NULL,            -- z. B. WR-20260619-AB12CD
    created_at   DATETIME        NOT NULL,            -- Eingangszeitpunkt
    name         VARCHAR(200)    NOT NULL,
    email        VARCHAR(254)    NOT NULL,
    telefon      VARCHAR(50)     NULL,
    vertrag      VARCHAR(200)    NOT NULL,            -- Vertrags-/Bestellnummer
    grund        TEXT            NULL,                -- optional, NIE Pflicht
    ip           VARBINARY(16)   NULL,                -- Dokumentation des Zugangs
    user_agent   VARCHAR(255)    NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_referenz (referenz),
    KEY idx_email (email),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
