# ACSF Setup

To configure a project to run on ACSF, perform the following steps after initially setting up Bolt:

1. Add a `hosting` variable set to `acsf` in `project.yml`
1. Execute `./bolt.sh acsf:init` from the project root.
1. Add the acsf module as a dependency to your installation profile.
