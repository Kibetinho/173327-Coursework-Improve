-- Create database (run as a superuser, or create manually if needed)
-- CREATE DATABASE coursework_db WITH ENCODING 'UTF8';

-- Switch to database before running (\c coursework_db)

CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone_number TEXT,
    user_name TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    user_type VARCHAR(32) NOT NULL CHECK (user_type IN ('Super_User','Administrator','Author')),
    access_time TIMESTAMP NULL,
    profile_image TEXT,
    address TEXT
);

CREATE TABLE IF NOT EXISTS articles (
    article_id SERIAL PRIMARY KEY,
    author_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    article_title TEXT NOT NULL,
    article_full_text TEXT NOT NULL,
    article_created_date TIMESTAMP NOT NULL DEFAULT NOW(),
    article_last_update TIMESTAMP NULL,
    article_display BOOLEAN NOT NULL DEFAULT TRUE,
    article_order INTEGER NOT NULL DEFAULT 0
);

-- Seed a Super_User account (password: super)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_name = 'super') THEN
        INSERT INTO users (full_name, email, phone_number, user_name, password_hash, user_type)
        VALUES ('Super User', 'super@example.com', NULL, 'super', crypt('super', gen_salt('bf')), 'Super_User');
    END IF;
END $$;


