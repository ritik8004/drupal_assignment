@javascript
Feature: Test Sign in and Forgot password features

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I initialize multilingual context
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "/ar/user/login"
    And I fill in field "edit-name" with dynamic "{username}"
    And I fill in field "edit-pass" with dynamic "{password}"
    And I press localized "sign in"
    Then I should see the localized link "My account"
    And I should see the localized link "Sign out"
    And I should see localized "recent orders"