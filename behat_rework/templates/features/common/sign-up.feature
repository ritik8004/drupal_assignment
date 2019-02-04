@javascript
Feature: Test Sign up features from -- common

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "{lang_reg_url}"