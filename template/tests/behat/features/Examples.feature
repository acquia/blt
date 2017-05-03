@example
Feature: Web drivers
  In order to verify that web drivers are working
  As a user
  I should be able to load the homepage
  With and without Javascript

  @javascript
  Scenario: Load a page with Javascript
    Given I am on "/"
    Then I should be on "/"

  Scenario: Load a page without Javascript
    Given I am on "/"
    Then the response status code should be 200
