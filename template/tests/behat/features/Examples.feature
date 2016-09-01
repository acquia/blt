@javascript
Feature: Selenium Test
  In order to test if Selenium is working
  As a user
  I need to be able to load the homepage

  Scenario: Load a page
    Given I am on "/"
    Then I should see the text "Log in"

@api @javascript @features
Feature: Features Module Configuration
  In order to test that the Features UI is not displaying conflicts.
  As an administrator
  I should not see changed, added, or conflicting features.

  Scenario Template: Administrators should see a list of features
    Given I am logged in as a user with the "export configuration" permission
    When I visit "/admin/config/development/features"
    And I select "<bundle_name>" from "bundle"
    And I wait for AJAX to finish
    Then I should not see text matching "Conflicts"
    And I should not see text matching "Changed"
    And I should not see text matching "Added"

    Examples:
      | bundle_name |
      | Default     |

# @todo Add @api example.
