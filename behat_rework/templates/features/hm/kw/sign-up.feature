@javascript
Feature: Test Sign up features for hm-kw

  @runthisonly
  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I initialize multilingual context
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "/user/register"