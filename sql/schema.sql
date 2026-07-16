-- ============================================================
--  Research Repository (ระบบคลังงานวิจัย) — schema
--  Target: MariaDB 10.4+ / PHP 8.2+  (utf8mb4 for Thai text)
--  NOTE: install.php runs this file. It is DESTRUCTIVE: it drops
--        existing tables so a re-run gives a clean database.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS research_files;
DROP TABLE IF EXISTS research_keywords;
DROP TABLE IF EXISTS research_authors;
DROP TABLE IF EXISTS research;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS = 1;

-- ---- categories (ประเภทงานวิจัย) --------------------------------
CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(255)  NOT NULL,
  enabled     TINYINT(1)    NOT NULL DEFAULT 1,
  sort_order  INT           NOT NULL DEFAULT 0,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- users (ผู้ใช้งาน) ------------------------------------------
--  role: ผู้ดูแลระบบ | ครู | ตัวแทนนักศึกษา
CREATE TABLE users (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(255)  NOT NULL,
  email          VARCHAR(255)  NOT NULL UNIQUE,
  password_hash  VARCHAR(255)  NOT NULL,
  role           VARCHAR(50)   NOT NULL DEFAULT 'ครู',
  dept           VARCHAR(255)  DEFAULT NULL,
  status         VARCHAR(20)   NOT NULL DEFAULT 'approved',  -- pending | approved | suspended
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- settings (การตั้งค่าระบบ) ----------------------------------
--  key/value store; e.g. require_approval = '0' | '1'
CREATE TABLE settings (
  setting_key    VARCHAR(64)   NOT NULL PRIMARY KEY,
  setting_value  TEXT          DEFAULT NULL,
  updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- research (งานวิจัย) ----------------------------------------
--  status: เผยแพร่ | รอตรวจสอบ | แบบร่าง | ไม่เผยแพร่
CREATE TABLE research (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  category_id    INT           DEFAULT NULL,
  dept           VARCHAR(255)  DEFAULT NULL,
  title_th       VARCHAR(500)  NOT NULL,
  title_en       VARCHAR(500)  DEFAULT NULL,
  abstract_th    TEXT          DEFAULT NULL,
  abstract_en    TEXT          DEFAULT NULL,
  pub_year       INT           DEFAULT NULL,
  academic_year  INT           DEFAULT NULL,
  status         VARCHAR(30)   NOT NULL DEFAULT 'แบบร่าง',
  created_by     INT           DEFAULT NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_research_status (status),
  INDEX idx_research_category (category_id),
  INDEX idx_research_year (pub_year),
  CONSTRAINT fk_research_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_research_user     FOREIGN KEY (created_by)  REFERENCES users(id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- research_authors (ผู้จัดทำ) --------------------------------
--  role: ผู้วิจัยหลัก | ผู้วิจัยร่วม | ครูที่ปรึกษา
CREATE TABLE research_authors (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  research_id  INT           NOT NULL,
  name         VARCHAR(255)  NOT NULL,
  role         VARCHAR(50)   NOT NULL DEFAULT 'ผู้วิจัยร่วม',
  sort_order   INT           NOT NULL DEFAULT 0,
  CONSTRAINT fk_author_research FOREIGN KEY (research_id) REFERENCES research(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- research_keywords (คำสำคัญ) --------------------------------
CREATE TABLE research_keywords (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  research_id  INT           NOT NULL,
  keyword      VARCHAR(255)  NOT NULL,
  sort_order   INT           NOT NULL DEFAULT 0,
  CONSTRAINT fk_keyword_research FOREIGN KEY (research_id) REFERENCES research(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- research_files (เอกสารแยกบท) -------------------------------
--  one row per chapter slot (chapter_index 0..8)
CREATE TABLE research_files (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  research_id    INT           NOT NULL,
  chapter_index  INT           NOT NULL,
  chapter_name   VARCHAR(255)  NOT NULL,
  stored_name    VARCHAR(255)  DEFAULT NULL,
  original_name  VARCHAR(255)  DEFAULT NULL,
  size_bytes     INT           DEFAULT NULL,
  is_public      TINYINT(1)    NOT NULL DEFAULT 0,
  uploaded       TINYINT(1)    NOT NULL DEFAULT 0,
  CONSTRAINT fk_file_research FOREIGN KEY (research_id) REFERENCES research(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
