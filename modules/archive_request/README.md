# Archive Request Module

**Type:** Custom Drupal 10 Module  
**Audience:** Content Editors, Senior Editors, Moderators, Product Owners

---

## Purpose

This module provides a structured process for requesting the archival and unarchival of content. It ensures that editors can flag content for removal from circulation and that senior team members can return archived content to an editable state when needed.

---

## Key Features

### 1. Request Archive (Editor-Initiated)
- Editors can request to archive both **published** and **unpublished** content.
- Action is permission-controlled and non-destructive.
- Optional reason field can be included.

### 2. Archive Dashboard (For Seniors/Moderators)
- Dashboard at `/admin/dashboard/archive-requests` lists all nodes flagged for archival.
- Includes filters for content status, type, and reason.

### 3. Request Unarchive (Senior-Initiated)
- Seniors use a form to flag archived content for return to **Draft** or **Published**.
- Reason field is optional.
- Editors can resume work after unarchive.

---

## Content Flow

### Archival Request (Editor)
1. Editor opens a node.
2. Clicks “Request Archive”.
3. Node flagged with `field_archive_request = TRUE`.
4. Senior reviews on dashboard.

### Unarchive Request (Senior)
1. Senior views archived content.
2. Opens “Request Unarchive” form.
3. Chooses target state: Draft or Published.
4. Node flagged with `field_unarchive_request = TRUE`.

---

## Permissions

| Permission                  | Who Uses It           | Purpose                                      |
|----------------------------|------------------------|----------------------------------------------|
| `request archive content`  | Editors                | Flag content for archive                     |
| `view archive dashboard`   | Senior Editors         | View archive requests                        |
| `manage unarchive request` | Senior Editors         | Flag content for return                      |

---

## Required Fields

| Field Name                    | Type       | Purpose                                      |
|------------------------------|------------|----------------------------------------------|
| `field_archive_request`      | Boolean    | Flag for archive request                     |
| `field_archive_request_reason` | Long Text | Optional editor reason                       |
| `field_unarchive_request`    | Boolean    | Flag for unarchive request                   |
| `field_unarchive_target_state` | Text List | Target: Draft or Published                   |
| `field_unarchive_reason`     | Long Text  | Optional senior reason                       |

---

## Views

1. **Archive Requests View**
   - Filter: `field_archive_request = TRUE`
   - Used by senior editors.

2. **Unarchive Requests View**
   - Filter: `field_unarchive_request = TRUE`
   - Used by editors or reviewers.

---

## Routes

- `/node/[nid]/archive-request` — Request archive  
- `/admin/dashboard/archive-requests` — Archive dashboard  
- `/node/[nid]/unarchive-request` — Request unarchive

---

## Integration

- Extend with ECA, email notifications, or workflows.
