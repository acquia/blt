@javascript
Feature: Selenium Test
  In order to test if Selenium is working
  As a user
  I need to be able to load the homepage

  Scenario: Load a page
    Given I am on "/"
    Then I should see the text "Log in"

# @todo Add @api example.
