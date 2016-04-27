# Domain Access Update

Author: Adam Malone

### Purpose

This cloud hook allows domains in domain_access module to be updated following 
database copy. This ensures no manual updates to the domains configuration are 
necessary after copying a db between environments.

### Example Scenario

### Installation Steps

1. Place file in `/hooks/common/post-db-copy`
