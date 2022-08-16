@javascript @createaccount @smoke @auth
Feature: Create new user account on the site

  Background:
    When I am on "{url_register}{behat_secret_key}"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: As an authenticated user, I should be able to sign in after providing valid credentials
    Given I fill in "edit-full-name" with "Nikita Jain"
    And I create an account with "{spc_new_registered_user_email}" using custom password
    And I wait 5 seconds
    And I uncheck the newsletter subscription checkbox
    And I click on "#edit-submit" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on homepage
    And I am on "user/login"
    And I wait 5 seconds
    And I wait for the page to load
    And I login with "{spc_new_registered_user_email}" using custom password
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/user" page
