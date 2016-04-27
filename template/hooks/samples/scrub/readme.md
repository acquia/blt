# Database Scrub

Author: Erik Webb

### Purpose

Scrubs important information from a Drupal database. E.g.,

1. Remove all email addresses.
2. Scrub url aliases for non-admins
3. Scrub webform submissions.
4. Remove passwords
5. Truncate cache tables.

### Example Scenario

### Installation Steps

1. Copy `db-scrub.sh` to `post-db-copy` for a given environment.
2. Set the execution bit to on e.g., `chmod a+x db-scrub.sh`
