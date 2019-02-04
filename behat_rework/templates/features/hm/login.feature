@javascript
Feature: Test Sign in and Forgot password features

  @signin {@tags}
  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I initialize multilingual context
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "{lang_login_url}"
    And I fill in field "edit-name" with dynamic "{var_username}"
    And I fill in field "edit-pass" with dynamic "{var_password}"
    And I press localized "sign in"
    Then I should see the localized link "My account"
    And I should see the localized link "Sign out"
    And I should see localized "recent orders"