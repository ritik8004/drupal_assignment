@homepage
Feature: Homepage
  I should be able to load the homepage
  With and without Javascript

  @javascript
  Scenario: Load a page with Javascript
    Given I am on "/"
    Then I should see the text "Sign in"

  Scenario: Load a page without Javascript
    Given I am on "/"
    Then I should see the text "Sign in"
