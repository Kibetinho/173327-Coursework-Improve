-- Create database (run as a superuser)
-- CREATE DATABASE coursework_db WITH ENCODING 'UTF8';

-- Switch to database before running (\c coursework_db)

-- users table
CREATE TABLE IF NOT EXISTS users (
    userId SERIAL PRIMARY KEY,
    Full_Name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone_Number VARCHAR(20),
    User_Name VARCHAR(50) UNIQUE,
    Password VARCHAR(255),
    UserType VARCHAR(20),
    AccessTime TIMESTAMP,
    profile_Image VARCHAR(255),
    Address TEXT
);

-- articles table
CREATE TABLE IF NOT EXISTS articles (
    articleId SERIAL PRIMARY KEY,
    authorId INTEGER REFERENCES users(userId),
    article_title VARCHAR(255),
    article_full_text TEXT,
    article_created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    article_last_update TIMESTAMP,
    article_display BOOLEAN,
    article_order INTEGER
);

-- Seed a Super_User account (password: super)
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM users WHERE User_Name = 'super') THEN
        INSERT INTO users (Full_Name, email, phone_Number, User_Name, Password, UserType)
        VALUES ('Super User', 'super@example.com', NULL, 'super', crypt('super', gen_salt('bf')), 'Super_User');
    END IF;
END $$;


