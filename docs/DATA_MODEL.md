# Data Model

## Purpose

This document defines the initial data model for the PIC EOP CMS.

It is intended to guide:

- migrations
- Eloquent models
- relationships
- admin forms
- permissions and workflow design

The model is designed for the MVP first, with room for future expansion.

## Modeling Principles

- use explicit tables for distinct domains
- avoid one generic content table for unrelated modules
- support multilingual content from the start
- keep publishing metadata consistent across public content modules
- keep auditability and maintainability built into the model

## MVP Domains

The MVP should cover:

- users and authentication
- roles and permissions
- settings and navigation
- pages
- news
- procurement announcements
- documents archive
- GRM submissions
- media
- subscriptions
- audit log

Authorization and media storage are implemented through established packages:

- `spatie/laravel-permission` for roles and permissions
- `spatie/laravel-medialibrary` for file and media management

## Shared Patterns

Most public content types should support:

- `status`
- `published_at`
- `archived_at`
- `created_by`
- `updated_by`
- SEO metadata
- locale-aware content

Recommended statuses:

- `draft`
- `published`
- `archived`

## Localization Strategy

For content modules, use a parent table plus translation table approach.

Reason:

- cleaner multilingual management
- supports per-locale titles and slugs
- avoids duplicating publication and ownership metadata

Pattern:

- base table stores global fields
- translation table stores locale-specific fields

Locales:

- `en`
- `tj`
- `ru`

## Core Tables

### users

Purpose:

- system users and administrators

Key fields:

- `id`
- `name`
- `email`
- `password`
- `email_verified_at`
- `two_factor_secret`
- `two_factor_recovery_codes`
- `remember_token`
- timestamps

Relationships:

- has many created records
- has many updated records
- belongs to many roles

### roles

Provided by `spatie/laravel-permission`.

Key fields:

- `id`
- `name`
- `guard_name`
- timestamps

### permissions

Provided by `spatie/laravel-permission`.

Key fields:

- `id`
- `name`
- `guard_name`
- timestamps

Examples:

- `pages.view`
- `pages.create`
- `pages.update`
- `pages.publish`
- `documents.download`
- `grm.manage`

### model_has_roles

Provided by `spatie/laravel-permission`.

### model_has_permissions

Provided by `spatie/laravel-permission`.

### role_has_permissions

Provided by `spatie/laravel-permission`.

## Settings and Navigation

### settings

Purpose:

- global CMS settings

Key fields:

- `id`
- `group`
- `key`
- `value`
- `type`
- timestamps

Examples:

- site name
- contact details
- social links
- analytics identifier
- homepage configuration

### menus

Purpose:

- logical menu containers

Key fields:

- `id`
- `name`
- `slug`
- `location`
- timestamps

Examples:

- main navigation
- footer navigation
- quick links

### menu_items

Purpose:

- menu tree nodes

Key fields:

- `id`
- `menu_id`
- `parent_id`
- `label`
- `url`
- `route_name`
- `target_type`
- `target_id`
- `locale`
- `sort_order`
- `is_active`
- timestamps

Notes:

- `target_type` and `target_id` may optionally point to internal records
- keep support for custom URLs

## Media and Files

### media

Provided by `spatie/laravel-medialibrary`.

Purpose:

- reusable uploaded assets attached directly to Eloquent models

Key fields:

- `id`
- `model_type`
- `model_id`
- `uuid`
- `collection_name`
- `name`
- `file_name`
- `mime_type`
- `disk`
- `conversions_disk`
- `size`
- `manipulations`
- `custom_properties`
- `generated_conversions`
- `responsive_images`
- `order_column`
- timestamps

Collections should be used to separate file intent, for example:

- `avatars`
- `cover`
- `gallery`
- `attachments`
- `documents`

This package replaces the need for a custom `mediaables` pivot table.

## SEO

### seo_metadata

Purpose:

- reusable SEO metadata for content entities

Key fields:

- `id`
- `seoable_type`
- `seoable_id`
- `locale`
- `title`
- `description`
- `keywords`
- `canonical_url`
- `robots`
- `image_media_id`
- timestamps

Reason:

- keeps SEO flexible without polluting every translation table too heavily

## Pages

### pages

Purpose:

- parent record for institutional and informational pages

Key fields:

- `id`
- `template`
- `status`
- `published_at`
- `archived_at`
- `sort_order`
- `is_home`
- `created_by`
- `updated_by`
- timestamps

### page_translations

Purpose:

- localized page content

Key fields:

- `id`
- `page_id`
- `locale`
- `title`
- `slug`
- `summary`
- `content`
- timestamps

Constraints:

- unique `page_id + locale`
- unique `locale + slug`

## News

### news

Purpose:

- project news and announcements

Key fields:

- `id`
- `status`
- `published_at`
- `archived_at`
- `featured_until`
- `created_by`
- `updated_by`
- timestamps

### news_translations

Purpose:

- localized news content

Key fields:

- `id`
- `news_id`
- `locale`
- `title`
- `slug`
- `summary`
- `content`
- timestamps

### news_categories

Purpose:

- grouping for news items

Key fields:

- `id`
- `slug`
- `is_active`
- timestamps

### news_category_translations

Purpose:

- localized category labels

Key fields:

- `id`
- `news_category_id`
- `locale`
- `name`
- timestamps

### news_news_category

Purpose:

- many-to-many news-category relation

Key fields:

- `news_id`
- `news_category_id`

## Procurement

### procurements

Purpose:

- procurement notices and lifecycle tracking

Key fields:

- `id`
- `reference_number`
- `procurement_type`
- `status`
- `published_at`
- `closing_at`
- `archived_at`
- `created_by`
- `updated_by`
- timestamps

Examples for `status`:

- `planned`
- `open`
- `closed`
- `awarded`
- `cancelled`
- `archived`

### procurement_translations

Purpose:

- localized procurement text

Key fields:

- `id`
- `procurement_id`
- `locale`
- `title`
- `slug`
- `summary`
- `content`
- timestamps

## Documents Archive

### document_categories

Purpose:

- document grouping

Key fields:

- `id`
- `slug`
- `is_active`
- timestamps

### document_category_translations

Purpose:

- localized document category labels

Key fields:

- `id`
- `document_category_id`
- `locale`
- `name`
- timestamps

### document_tags

Purpose:

- tagging for filtering and search

Key fields:

- `id`
- `slug`
- timestamps

### document_tag_translations

Purpose:

- localized tag labels

Key fields:

- `id`
- `document_tag_id`
- `locale`
- `name`
- timestamps

### documents

Purpose:

- public downloadable files

Key fields:

- `id`
- `document_category_id`
- `status`
- `published_at`
- `archived_at`
- `file_type`
- `document_date`
- `created_by`
- `updated_by`
- timestamps

### document_translations

Purpose:

- localized document metadata

Key fields:

- `id`
- `document_id`
- `locale`
- `title`
- `slug`
- `summary`
- timestamps

### document_document_tag

Purpose:

- many-to-many document-tag relation

Key fields:

- `document_id`
- `document_tag_id`

Note:

- a `Document` model should implement `HasMedia`
- the primary downloadable file should live in a dedicated media collection such as `documents`

## GRM

### grm_submissions

Purpose:

- public grievance and feedback intake

Key fields:

- `id`
- `reference_number`
- `name`
- `email`
- `phone`
- `subject`
- `message`
- `status`
- `submitted_at`
- `reviewed_at`
- `resolved_at`
- `assigned_to`
- timestamps

Examples for `status`:

- `new`
- `under_review`
- `in_progress`
- `resolved`
- `closed`

### grm_notes

Purpose:

- internal admin notes on submissions

Key fields:

- `id`
- `grm_submission_id`
- `user_id`
- `note`
- timestamps

## Staff Directory

This module may be phase 2, but the schema direction should already be clear.

### staff_members

Purpose:

- public staff records

Key fields:

- `id`
- `parent_id`
- `department`
- `position_order`
- `email`
- `public_email`
- `phone`
- `is_active`
- timestamps

### staff_member_translations

Purpose:

- localized staff text

Key fields:

- `id`
- `staff_member_id`
- `locale`
- `full_name`
- `position_title`
- `bio`
- timestamps

## Subscriptions

### subscription_topics

Purpose:

- configurable topics users can subscribe to

Key fields:

- `id`
- `name`
- `slug`
- `is_active`
- timestamps

Examples:

- news
- procurement
- what_is_new

### subscriptions

Purpose:

- subscriber list

Key fields:

- `id`
- `email`
- `name`
- `locale`
- `is_confirmed`
- `confirmed_at`
- `unsubscribed_at`
- timestamps

### subscription_subscription_topic

Purpose:

- many-to-many subscription topic relation

Key fields:

- `subscription_id`
- `subscription_topic_id`

## Auditability

### audit_logs

Purpose:

- track significant administrative actions

Key fields:

- `id`
- `user_id`
- `event`
- `auditable_type`
- `auditable_id`
- `old_values`
- `new_values`
- `ip_address`
- `user_agent`
- `created_at`

Examples for `event`:

- `created`
- `updated`
- `published`
- `archived`
- `deleted`
- `login`
- `logout`

## Recommended Relationships Summary

- `users` belongs to many `roles` via `spatie/laravel-permission`
- `roles` belongs to many `permissions` via `spatie/laravel-permission`
- `pages` has many `page_translations`
- `news` has many `news_translations`
- `news` belongs to many `news_categories`
- `procurements` has many `procurement_translations`
- `documents` has many `document_translations`
- `documents` belongs to one `document_category`
- `documents` belongs to many `document_tags`
- `media` belongs morphically to content records through `spatie/laravel-medialibrary`
- `grm_submissions` has many `grm_notes`
- most managed records belong to `created_by` and `updated_by` users

## Indexing Guidance

Add indexes for:

- `status`
- `published_at`
- `archived_at`
- `locale`
- `slug`
- foreign keys
- common filter columns such as procurement status and document category

Add unique constraints for:

- translation table `parent_id + locale`
- localized slugs where public routes depend on slug uniqueness
- role slugs
- permission slugs
- menu slugs
- topic slugs

## MVP Implementation Priority

Recommended migration order:

1. users enhancements
2. permission package tables
3. audit_logs
4. settings
5. menus and menu_items
6. media library tables
7. seo_metadata
8. pages and page_translations
9. news, categories, and translations
10. procurements and translations
11. document categories, tags, documents, and translations
12. grm_submissions and grm_notes
13. subscription topics and subscriptions

## Open Decisions

These should be finalized before coding or during the first implementation pass:

- which media collections each domain model should expose
- whether SEO remains centralized in `seo_metadata` or is embedded into translation tables
- whether GRM should support file attachments in MVP
- whether staff directory is MVP or phase 2
- exact permission matrix per module

## Next Step

After approval of this data model, implementation should begin with:

- roles and permissions
- audit logging
- settings and navigation
- media
- pages

This creates the foundation for the rest of the CMS.
