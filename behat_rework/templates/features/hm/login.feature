@javascript
Feature: Test Sign in feature for h&m brand

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I initialize multilingual context
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "/ar/user/login"
    When I fill in "edit-name" with "{username}"
    And I fill in "edit-pass" with "{password}"
    And I press localized "sign in"
    Then I should see the localized link "My account"
    And I should see the localized link "Sign out"
    And I should see localized "recent orders"