# RescueNet Pakistan - Disaster Relief Coordination Platform

disaster-relief-final is a disaster-relief coordination application.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `affected_families`, `aid_distribution`, `camp_needs`, `donations`, `family_camp_link`, `family_losses`, `family_special_needs`, `relief_camps`, `resources`, `task_assignments`, `users`, and related records.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/add_camp.php`](admin/add_camp.php), [`admin/add_users.php`](admin/add_users.php), [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_navbar.php`](admin/admin_navbar.php) |
| Camp Manager | [`camp_manager/dashboard.php`](camp_manager/dashboard.php), [`camp_manager/families.php`](camp_manager/families.php), [`camp_manager/manage_aid.php`](camp_manager/manage_aid.php), [`camp_manager/manage_needs.php`](camp_manager/manage_needs.php), [`camp_manager/manage_tasks.php`](camp_manager/manage_tasks.php) |
| Field Staff | [`field_staff/dashboard.php`](field_staff/dashboard.php), [`field_staff/manage_families.php`](field_staff/manage_families.php), [`field_staff/manage_family_camp.php`](field_staff/manage_family_camp.php), [`field_staff/manage_family_losses.php`](field_staff/manage_family_losses.php), [`field_staff/manage_special_needs.php`](field_staff/manage_special_needs.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php) |
| Supporter | [`supporter/dashboard.php`](supporter/dashboard.php), [`supporter/manage_donations.php`](supporter/manage_donations.php), [`supporter/notifications.php`](supporter/notifications.php), [`supporter/profile.php`](supporter/profile.php), [`supporter/transparency_reports.php`](supporter/transparency_reports.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/disaster-relief-final.git
cd disaster-relief-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/disaster_db.sql`](Database/disaster_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`camp_manager/config.php`](camp_manager/config.php), [`config.php`](config.php), [`field_staff/config.php`](field_staff/config.php), [`supporter/config.php`](supporter/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
