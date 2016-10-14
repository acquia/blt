Feature: Web drivers
  In order to verify that web drivers are working
  As a user
  I should be able to load the homepage
  With and without Javascript

  @javascript
  Scenario: Load a page
    Given I am on "/"
    Then I should see the text "Log in"

  Scenario: Load a page
    Given I am on "/"
    Then I should see the text "Log in"
