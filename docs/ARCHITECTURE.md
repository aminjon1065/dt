# Architecture

## Purpose

This document defines the target architecture for the PIC EOP website platform.

The system is a multilingual institutional CMS built as a Laravel monolith. It is intended to support public communication, project transparency, document publishing, procurement announcements, grievance intake, and internal content administration.

This is not an API-first system and not a simple marketing website. It is a content platform with structured administration, public information services, and long-term maintainability requirements.

## Primary Goals

- deliver a multilingual public website in English, Tajik, and Russian
- enable PIC staff to manage content without developer assistance
- support structured publishing of pages, news, procurement information, documents, and GRM submissions
- comply with accessibility, security, and operational requirements from the RFQ
- provide a maintainable platform for future expansion

## Architectural Style

- Laravel monolith
- server-side routing with Inertia
- React frontend for both public and administrative interfaces
- MySQL as the primary relational database
- local or object-based file storage for media and documents
- background jobs for notifications, scheduled publishing, and housekeeping tasks

The architecture should favor clear module boundaries inside one application rather than splitting functionality into separate services.

## Technology Stack

### Backend

- PHP 8.4
- Laravel 13
- MySQL
- Laravel queues and scheduler
- Laravel Fortify for authentication

### Frontend

- Inertia.js
- React
- TypeScript
- Tailwind CSS

### Integrations

- email delivery for notifications and subscriptions
- optional Telegram notifications
- analytics integration such as Google Analytics

## System Scope

The platform must support at minimum the following domains:

- public pages
- news and updates
- procurement announcements
- document archive
- media content
- staff directory
- grievance redress mechanism
- subscriptions and notifications
- content administration and auditability

## High-Level Module Map

### Foundation Modules

- Authentication
- Users
- Roles and Permissions
- Settings
- Navigation
- SEO Metadata
- Media Management
- Audit Log
- Subscriptions

### Content Modules

- Pages
- News
- Procurement
- Documents
- Media Section
- Staff Directory
- GRM

### Support Modules

- Search
- Sitemap
- Structured Data
- Analytics
- Backups
- Scheduled Tasks

## Core Design Principles

### CMS-First

The administrative experience is a core product surface. Content must be manageable by PIC staff through structured forms, editors, filters, and workflows.

### Monolith with Clear Boundaries

All modules live in one Laravel codebase, but each domain should be kept conceptually isolated through dedicated models, requests, actions, policies, routes, and frontend pages.

### Multilingual by Design

English, Tajik, and Russian are first-class requirements. Content architecture must support localization from the beginning, not as an afterthought.

### Structured Publishing

Content should not be modeled as one generic blob. Pages, news, procurement notices, documents, and staff entries have different lifecycles and metadata and should be represented explicitly.

### Operational Simplicity

The solution should remain understandable and supportable by a small team over time. Avoid unnecessary abstractions, premature service extraction, and hidden system complexity.

## Public Information Architecture

The public website should be organized around the following top-level areas:

- homepage
- about and institutional pages
- projects or initiatives information
- news and updates
- procurement announcements
- documents archive
- media and outreach content
- staff and contacts
- grievance submission

The homepage should aggregate recent and important content from multiple modules, including news, procurement, documents, and featured updates.

## Administrative Architecture

The administrative interface should provide:

- dashboard overview
- module-specific content management screens
- global search and filtering where useful
- media upload and reuse
- activity tracking
- role-based access control
- publication and archive controls
- multilingual editing workflows

Admin users should not need direct database access or developer intervention for routine content operations.

## Roles and Permissions

The initial role model should include:

- Admin
- Editor
- Contributor

### Admin

- full access to all modules
- publish and archive authority
- user and settings management
- audit visibility

### Editor

- create and edit content in assigned modules
- manage drafts and updates
- limited publishing ability only if explicitly granted

### Contributor

- create and edit own draft content
- no final publishing authority by default

Even if current workflow is admin-led publishing, permissions should still be granular enough to support future delegation.

## Content Model

The platform should use explicit content entities instead of a single generic content table.

### Pages

Used for institutional and informational content.

Typical fields:

- title
- slug
- locale
- summary
- body or content blocks
- seo fields
- status
- published_at

### News

Used for updates, announcements, and project communication.

Typical fields:

- title
- slug
- locale
- summary
- content
- cover image
- published_at
- archive state
- category or tags

### Procurement Announcements

Used for procurement-related publishing and tracking.

Typical fields:

- title
- slug
- locale
- procurement type
- status
- publication date
- closing date
- description
- attached documents

### Documents

Used for archive and downloads.

Typical fields:

- title
- locale
- document category
- tags
- file reference
- file type
- publication date
- related module reference

### Staff Directory

Used for public staff and structure information.

Typical fields:

- full name
- position
- department or unit
- parent in hierarchy
- email
- obfuscated public email
- phone
- profile image

### GRM Submissions

Used for grievance and feedback intake.

Typical fields:

- submitter name
- contact information
- subject
- message
- submission status
- internal notes
- assigned admin
- resolution timestamps

## Shared Content Concerns

Most public content types should support:

- multilingual values
- draft and published states
- archived state where relevant
- SEO title and description
- social preview image where relevant
- created_by and updated_by
- published_at

## Multilingual Strategy

The platform must treat localization as a core concern.

Requirements:

- support `en`, `tj`, and `ru`
- localized URLs
- localized navigation labels
- localized SEO metadata
- ability to publish content per locale

Recommended approach:

- keep locale-aware content at the data model layer
- standardize route prefixes by locale
- ensure each public module resolves records by localized slug or locale-aware identifier

## Publishing Workflow

Initial workflow should support:

- draft
- published
- archived

Behavior:

- content is created as draft
- admin reviews and publishes
- time-sensitive records can be archived automatically or manually
- scheduled publication should be supported where needed

This is sufficient for MVP and can be extended later if approval chains become more formal.

## Media and File Management

The system must support both reusable media and downloadable documents.

Media requirements:

- upload images, videos, and general files
- attach assets to multiple content records where appropriate
- validate file types and size limits
- preserve metadata useful for administration

Document archive requirements:

- category and tag filters
- searchable title and metadata
- public download support
- relation to procurement, pages, or news where required

## Search Strategy

The public site must support search at least for documents and content listings.

Initial approach:

- MySQL-backed search using indexed fields
- module-specific filtering for documents, procurement, and news

Future enhancement:

- move to a dedicated search engine only if scale or relevance quality requires it

## Notifications and Communications

The platform should support:

- email subscriptions by topic or section
- transactional email where needed
- optional Telegram notifications

Notification-related work should run asynchronously through queues where possible.

## Security Architecture

The platform must follow secure defaults.

Requirements:

- HTTPS-only deployment
- CSRF protection
- strict server-side validation
- authorization policies per module
- audit logging of administrative actions
- secure file handling
- protection against common OWASP categories

Audit logging is a required part of the system, not an optional enhancement.

## Accessibility and Frontend Quality

The platform must be designed for:

- WCAG 2.1 AA baseline compliance
- keyboard navigation
- semantic markup
- sufficient color contrast
- screen reader compatibility
- responsive behavior across desktop, tablet, and mobile
- acceptable experience for low-bandwidth users

## Operational Architecture

The system must include operational support for:

- scheduled jobs
- daily backups
- deployment to staging and production
- error logging
- maintenance reporting support
- broken link checking
- accessibility issue monitoring where practical

## Data Ownership and Maintainability

All source code, assets, documentation, and deliverables belong to PIC EOP.

The architecture should favor:

- readable domain code
- explicit module boundaries
- testable business logic
- minimal hidden framework magic
- maintainable admin workflows

## MVP Boundary

The first implementation milestone should include:

- authentication
- roles and permissions
- settings and navigation
- audit log
- media uploads
- pages
- news
- procurement announcements
- document archive
- GRM submissions
- multilingual public frontend
- basic subscriptions

The following can be implemented after the CMS core is stable:

- staff directory
- advanced media section
- Telegram notifications
- MIS or custom dashboard integrations
- richer analytics reporting

## Recommended Delivery Order

1. CMS foundation
2. roles, permissions, and audit log
3. multilingual and SEO foundation
4. media management
5. pages
6. news
7. procurement
8. documents archive
9. GRM
10. homepage aggregation and public navigation
11. subscriptions and notifications
12. staff directory and secondary features
13. hardening, accessibility, testing, and release

## Architectural Constraints

- do not split into microservices
- do not introduce API-first patterns unless a clear integration need appears
- do not rely on admin operations that require developer intervention
- do not postpone multilingual or accessibility concerns until late implementation

## Next Step

The next design artifact after this document should define:

- entity-by-entity data model
- table-level schema direction
- module backlog for MVP
- permission matrix

Once that is approved, implementation should begin with the CMS foundation.
