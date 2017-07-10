@homepage @smoke
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

  Scenario: As a Guest user
  I should be able to view the header and the footer

    Given I am on homepage
    Then I should be able to see the header
    And I should be able to see the footer
    And the page title should be "Home | Alshaya"

  @javascript
  Scenario: As a Guest user
  I should be prompted with warning messages
  when I try to sign in without submitting any credentials

    Given I go to "user/login"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "Email address is required."
    And I should see "Password is required."

  @javascript
  Scenario: As a Guest user
  I should be able to subscribe with Mothercare

    Given I am on homepage
    When I subscribe using a valid Email ID
    And I press "sign up"
    And I wait for AJAX to finish
    Then I should see "Thank you for your subscription."
