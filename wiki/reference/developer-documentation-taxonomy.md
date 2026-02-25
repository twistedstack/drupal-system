---
title: Developer Documentation Taxonomy
tags:
  - documentation
  - standards
  - drupal
  - acquia
  - azure-devops
  - governance
updated: 2025-10-25
status: draft
---

# 🧭 Developer Documentation Taxonomy

A unified structure for DAU developer documentation across Drupal, Acquia Cloud, and Azure DevOps.
Defines how guides, references, and templates are grouped and tagged for easier navigation and governance.

## 1️⃣ Environment Setup
Covers everything for getting started with Acquia Cloud IDEs and local environments.

Includes:
- Creating and authenticating Acquia Cloud IDEs
- Connecting to Azure DevOps
- Managing API tokens and SSH keys
- Configuring local machines for Drupal (Composer, PHP, Drush, memory limits, etc.)

Tags: `acquia`, `ide`, `auth`, `setup`, `local-dev`, `composer`, `php`, `drush`, `env`

## 2️⃣ Troubleshooting
Quick fixes and workarounds for environment and build issues.

Includes:
- Resolving Gulp and Node/NPM version conflicts in themes
- Patching Composer lock and sanitization issues in Acquia pipelines

Tags: `gulp`, `nvm`, `npm`, `theme-build`, `composer`, `drush`, `fix`, `sanitization`

## 3️⃣ Command Reference
Central location for developer command references and cheat sheets.

Includes:
- Drush, Git, and Acquia CLI commands
- IDE and Azure DevOps command examples

Tags: `drush`, `acli`, `git`, `azure`

## 4️⃣ Developer Procedures & Norms
Standards and templates for consistent development and governance.

Includes:
- Module request and LOE templates
- README format and developer checklists
- Review and merge standards

Tags: `dev-process`, `templates`, `loe`, `governance`

## 5️⃣ DevOps Integration
Guides for connecting Drupal development with Azure DevOps workflows.

Includes:
- Azure repo integration and branch strategy
- CI/CD pipeline management
- Deployment workflows

Tags: `azure-devops`, `ci-cd`, `git`, `integration`

# 🧱 Recommended Folder Hierarchy for `/docs/`
/docs
  /environment
  /troubleshooting
  /process
  /templates
  /reference

# 🧩 Integration with Developer Governance
These existing docs can be embedded as procedural appendices within the Developer Procedures & Norms package.

- Environment Setup & Authentication: Acquia-Cloud-IDEs.md, IDE Token.md, Local-Environment-Setup.md
- Troubleshooting & Maintenance: Fixing-issue-with-gulp-not-working.md, Instructions-to-Fix-Sanitization-Issue.md
- Daily Workflow Reference: IDE Token.md (CLI section)
- Governance Templates: module-request-template.md, loe-template.md, definition-of-done.md

## 📄 Metadata
- URL: https://linear.app/rock-master/issue/MAS-67/developer-documentation-taxonomy
- Project: https://linear.app/rock-master/project/wiki-ec356ed90975
- Status: Backlog
- Priority: None
- Assignee: Unassigned
- Created: 2025-10-25
- Updated: 2025-10-25
