# CMS Implementation Plan

## Goal

Build a multilingual institutional CMS platform for PIC EOP based on the RFQ requirements.

The system is not just a presentation website. It must support:

- public information publishing
- document management
- procurement announcements
- GRM submissions
- staff and organizational information
- multilingual content in English, Tajik, and Russian
- role-based content administration
- auditability, accessibility, and long-term support

## Target Architecture

- Laravel 13 monolith
- Inertia.js + React frontend
- MySQL database
- CMS-first architecture, not API-first
- file storage for documents and media
- email notifications and optional Telegram notifications

## Delivery Strategy

Implementation should be done in layers, not page-by-page. This reduces rework and establishes reusable CMS foundations first.

## Phase 1: Discovery and Specification

Purpose: turn RFQ requirements into an actionable product and technical scope.

Deliverables:

- approved module list
- sitemap and navigation structure
- entity map and content model
- MVP scope
- Software Requirements Specification
- wireframes for homepage and key internal pages

Main tasks:

- define all required public sections
- define admin roles and permissions
- define multilingual content rules
- define publishing workflow
- define required reports, filters, and dashboards
- define search and document archive behavior
- define GRM submission flow
- define procurement content structure

## Phase 2: CMS Foundation

Purpose: establish the reusable platform core used by all content modules.

Core capabilities:

- authentication and admin access
- roles and permissions
- multilingual content support
- slugs and localized routing strategy
- SEO metadata fields
- publication status and scheduling
- global settings
- navigation and menus
- reusable media uploads
- audit logging
- subscription management

Suggested core entities:

- users
- roles
- permissions
- settings
- menus
- menu_items
- media
- audit_logs
- subscriptions

Cross-cutting content attributes:

- title
- slug
- locale
- status
- published_at
- archived_at
- seo_title
- seo_description
- seo_image

## Phase 3: Admin Panel

Purpose: provide a practical internal interface for PIC staff to manage content without developer help.

Main features:

- admin dashboard
- activity tracking with filters by date, user, and content type
- reusable CRUD screens
- search and filters in admin lists
- WYSIWYG and block-based editing
- real-time preview
- media picker
- locale switcher
- scheduling controls
- archive controls

## Phase 4: Core Content Modules

Purpose: implement the highest-priority RFQ modules that make the CMS operational.

Modules:

- Pages
- News
- Procurement Announcements
- Documents Archive
- GRM

### Pages

Capabilities:

- create and manage static and semi-static pages
- multilingual content
- SEO metadata
- structured content blocks
- draft and published states

### News

Capabilities:

- categories or tags
- publish scheduling
- archive support
- homepage visibility
- social sharing support

### Procurement Announcements

Capabilities:

- procurement type
- status tracking
- key dates
- attachments
- filtering on public listing pages
- lifecycle visibility

### Documents Archive

Capabilities:

- upload and download documents
- categories, tags, and filters
- searchable archive
- multi-format file support
- relation to pages, news, and procurement records

### GRM

Capabilities:

- public submission form
- status tracking
- moderation and review workflow
- secure storage
- admin filtering and follow-up

## Phase 5: Public Website

Purpose: build the public-facing multilingual experience on top of CMS-managed content.

Main public sections:

- homepage
- institutional pages
- news listing and detail pages
- procurement listing and detail pages
- documents archive
- GRM submission page
- staff directory
- media section
- contact and informational pages

Homepage requirements:

- featured content
- what is new section
- quick access navigation
- latest procurement and news
- important documents or highlights
- subscription entry points

## Phase 6: Secondary Modules and Enhancements

Purpose: complete the wider RFQ scope after the core modules are stable.

Modules and features:

- staff directory with organizational hierarchy
- media library and media section
- email subscriptions
- optional Telegram notifications
- auto-archiving of time-sensitive content
- sitemap generation
- structured data support
- analytics integration
- broken link monitoring
- accessibility issue monitoring
- optional dashboard or MIS integration if required

## Phase 7: Non-Functional Requirements

Purpose: align implementation with the compliance and operational expectations in the RFQ.

### Accessibility

- WCAG 2.1 AA baseline
- keyboard navigability
- proper semantic structure
- contrast compliance
- screen-reader-friendly UI

### Security

- SSL-only deployment
- CSRF protection
- strong validation
- XSS protection
- role-based authorization
- audit logging

### Performance

- low-bandwidth optimization
- responsive images and asset discipline
- efficient frontend payloads
- caching where appropriate

### Compliance

- W3C standards
- OWASP best practices
- privacy-conscious data handling

### Operations

- daily backups
- disaster recovery guidance
- secure file storage strategy
- maintenance reporting support

## Phase 8: Testing, Training, and Release

Purpose: complete the system for handover and long-term support.

Main tasks:

- Pest feature tests for critical admin and public flows
- smoke testing of key pages
- validation of multilingual behavior
- accessibility review of core pages
- training materials for PIC staff
- admin guide
- user guide
- backup and recovery guide
- final deployment and handover

## MVP Recommendation

The first production-capable milestone should include:

- authentication
- roles and permissions
- audit logging
- media uploads
- settings and navigation
- pages
- news
- procurement announcements
- documents archive
- GRM
- multilingual public frontend
- email subscriptions

The following can follow after MVP if needed:

- advanced media section
- Telegram notifications
- MIS or dashboard integration
- deeper analytics and reporting enhancements

## Recommended Development Order

1. Discovery and SRS
2. Roles, permissions, settings, and audit log
3. Media and multilingual foundation
4. Pages
5. News
6. Procurement announcements
7. Documents archive
8. GRM
9. Public homepage and navigation
10. Staff directory
11. Subscriptions and notifications
12. Accessibility, SEO, backups, and release hardening

## Immediate Next Step

Before implementation starts, the project should approve:

- final module list
- MVP boundary
- content model for each module
- admin roles and permissions
- multilingual strategy
- publishing workflow

Once approved, development should start with the CMS foundation rather than visual pages.
