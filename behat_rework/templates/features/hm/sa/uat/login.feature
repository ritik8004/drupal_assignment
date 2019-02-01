@javascript
Feature: Test Sign in and Forgot password features

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "/user/login"
    When I fill in "edit-name" with "{username}"
    And I fill in "edit-pass" with "{password}"
    And I press "sign in"
    Then I should see the link "My account"
    And I should see the link "Sign out"
    And I should see "recent orders"