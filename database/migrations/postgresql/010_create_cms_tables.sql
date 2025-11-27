
CREATE TABLE IF NOT EXISTS "pages" (
  "id" SERIAL PRIMARY KEY,
  "title" VARCHAR(255) NOT NULL,
  "slug" VARCHAR(255) NOT NULL UNIQUE,
  "content" LONGTEXT,
  "excerpt" TEXT,
  "template" VARCHAR(100) DEFAULT 'default',
  "status" ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  "is_homepage" BOOLEAN DEFAULT FALSE,
  "meta_title" VARCHAR(255),
  "meta_description" VARCHAR(500),
  "meta_keywords" VARCHAR(255),
  "author_id" INT UNSIGNED,
  "published_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("author_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_slug" ("slug"),
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "blog_posts" (
  "id" SERIAL PRIMARY KEY,
  "title" VARCHAR(255) NOT NULL,
  "slug" VARCHAR(255) NOT NULL UNIQUE,
  "content" LONGTEXT NOT NULL,
  "excerpt" TEXT,
  "featured_image" VARCHAR(255),
  "status" ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  "author_id" INT UNSIGNED,
  "view_count" INT DEFAULT 0,
  "meta_title" VARCHAR(255),
  "meta_description" VARCHAR(500),
  "published_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("author_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_slug" ("slug"),
  INDEX "idx_status" ("status"),
  INDEX "idx_published_at" ("published_at")
);

CREATE TABLE IF NOT EXISTS "blog_categories" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(100) NOT NULL,
  "slug" VARCHAR(100) NOT NULL UNIQUE,
  "description" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "blog_post_categories" (
  "post_id" INTEGER NOT NULL,
  "category_id" INTEGER NOT NULL,
  PRIMARY KEY ("post_id", "category_id"),
  FOREIGN KEY ("post_id") REFERENCES "blog_posts"("id") ON DELETE CASCADE,
  FOREIGN KEY ("category_id") REFERENCES "blog_categories"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "blog_tags" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(50) NOT NULL UNIQUE,
  "slug" VARCHAR(50) NOT NULL UNIQUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "blog_post_tags" (
  "post_id" INTEGER NOT NULL,
  "tag_id" INTEGER NOT NULL,
  PRIMARY KEY ("post_id", "tag_id"),
  FOREIGN KEY ("post_id") REFERENCES "blog_posts"("id") ON DELETE CASCADE,
  FOREIGN KEY ("tag_id") REFERENCES "blog_tags"("id") ON DELETE CASCADE
);
