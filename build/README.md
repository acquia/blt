# BLT Build Process

Given that BLT generates child projects which must be tested, the CI flow is a little confusing. The CI configuration is detailed below.

## CI Flow

When a pull request is submitted a travis build is run against BLT. This tests BLT's ability to generate a new project. After a successful build, the new project will be deployed to Acquia Cloud and to a GitHub repository, where another child Travis build is subsequently executed. Here is the step-by-step breakdown:

1. BLT 7.x Pull Request is submitted
2. Travis Build *against BLT* creates BLTed7 child project
    * Tests are run to assert that project was created
    * Tests are run against the child project (install, behat, phpunit, etc.)
3. Upon success, BLTed7 child project is pushed to ACE blted 7 subscription. 
    * Tests assert that deployment to remote(s) was successful
4. Travis Build *against BLTed7* begins. Sadly, failure of this build has no impact on the success of BLT's builds. Status of child builds should be checked periodically to verify that BLT is generating a working build process for child projects out of the box.

Likewise, this process occurs for pull requests submitted to BLT 8.x with BLTed8 as a companion project.
