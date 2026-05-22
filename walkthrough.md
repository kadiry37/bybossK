# By Boss Mimarlık Mobilya - Migration and Verification Walkthrough

This document outlines the codebase changes made during the rebranding and migration process of the `Kadir2026` codebase to the brand **By Boss Mimarlık Mobilya** (`bybossmimarlik.com`), and details the step-by-step deployment instructions for the hosting transition.

## Changes Made

### 1. Astro Frontend Configuration & Code Sweep
- **Domain Configuration**: Updated `astro.config.mjs` site parameter to `https://bybossmimarlik.com`.
- **API Client & Resolvers**: Updated `src/lib/api.ts` to dynamically resolve uploads and resource paths pointing to `bybossmimarlik.com` instead of the old domain.
- **Component Brand Updates**:
  - `Navbar.tsx`: Updated brand label defaults, phone/email support constants, and logos to output **BY BOSS MİMARLIK MOBİLYA**.
  - `Footer.tsx`: Rebranded tagline, copyright ownership, address context, support telephone number `0 532 567 4537`, and WhatsApp integration `905325674537`.
  - `Contact.tsx`: Updated contact form endpoint and fallback email support records.
- **JSON-LD Schema, Canonical & Language Tags**: Sweeped and modernized schema metadata on all landing, list, detail, and localized pages:
  - `Layout.astro`: Updated canonical mappings, WebSite schemas, organization schemas, and search query templates.
  - `index.astro`, `blog.astro`, `tarihce.astro`, `urunler.astro`, `projeler.astro`, `hizmetler.astro`, and detail paths: corrected structured data definitions and alternate language hreflangs.

### 2. PHP Backend & Database Restructuring
- **Dynamic Action dispatch**: Refactored `config.php` to retrieve GitHub parameters (`GITHUB_TOKEN`, `GITHUB_REPO_OWNER`, `GITHUB_REPO_NAME`) from the environment variables, removing all hardcoded GitHub profile information.
- **CORS Configuration**: Updated backend configuration permissions to allow the new domains: `bybossmimarlik.com` and `www.bybossmimarlik.com`.
- **IndexNow Integration**: Replaced search engine index targets with `bybossmimarlik.com` in `indexnow.php`.
- **Seed Data Update**: Revised the template SQL database file `database.sql` to preset the new brand metadata, support info, and customized timeline parameters (2012-2025 chronology reflecting architectural history).

---

## What Was Tested

### 1. Astro Compilation & Build Execution
To verify that all template updates, TypeScript properties, schema references, and localized routes built successfully without syntax errors, we ran:
```powershell
npm run build
```
- **Fallback Verification**: The compilation was successfully executed against a working fallback API to test production generation logic.
- **Results**: **239 static pages** were compiled successfully in **15.43s** with zero errors or warnings.
- **Cleanup**: Reverted the test URL configuration in `src/lib/api.ts` to point back to the production API `https://bybossmimarlik.com/api`.

---

## Hosting and Deployment Integration Guide

Follow these steps to deploy the application on your new DirectAdmin hosting:

### Step 1: Database Setup in DirectAdmin
1. Log in to your DirectAdmin control panel.
2. Navigate to **MySQL Management** (or *Veritabanı Yönetimi*) and click **Create New Database**.
3. Choose a database name and user, and generate a strong password. Save these credentials.
4. Open **phpMyAdmin**, select the newly created database, and click the **Import** (İçe Aktar) tab.
5. Upload and execute the updated schema and seed file:
   [database.sql](file:///c:/Users/USER/Desktop/Kadir2026/php-backend/sql/database.sql)

### Step 2: Server-Side Environment Variables (.env)
1. On your server, navigate inside the `public_html/` folder (or equivalent backend root).
2. Create a file named `.env` based on the template:
   [php-backend/.env.example](file:///c:/Users/USER/Desktop/Kadir2026/php-backend/.env.example)
3. Fill out the values:
   ```env
   DB_HOST=localhost
   DB_NAME=your_new_database_name
   DB_USER=your_new_database_user
   DB_PASS=your_new_database_password

   # Set these if you wish to trigger automatic builds on admin updates:
   GITHUB_TOKEN=your_github_personal_access_token
   GITHUB_REPO_OWNER=your_github_username_or_org
   GITHUB_REPO_NAME=your_github_repo_name
   ```
4. Upload `php-backend/api/config.php` manually to your server under `public_html/api/config.php` (it is gitignored to avoid leaking config details).

### Step 3: Configure GitHub Secrets
In your new GitHub repository, navigate to **Settings > Secrets and variables > Actions** and create the following Repository Secrets:
* `FTP_SERVER`: The hostname of your DirectAdmin FTP (e.g., `ftp.bybossmimarlik.com` or server IP).
* `FTP_USERNAME`: Your FTP user account name.
* `FTP_PASSWORD`: Your FTP user account password.
* `API_URL`: `https://bybossmimarlik.com/api` (used by Astro during compilation to fetch settings).

### Step 4: Push to Deploy
Once your code is pushed to the `main` branch of your new repository:
1. The GitHub Action in `.github/workflows/deploy.yml` will automatically build the Astro project.
2. It will deploy the compiled static files to the server.
3. Your brand new site will be fully operational at `https://bybossmimarlik.com`!
