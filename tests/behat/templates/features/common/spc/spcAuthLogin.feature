@javascript @hmkwdev @mckwuat @pbkkwuat @pbksauat @pbkaeuat
Feature: Test Sign in and Forgot password features from common

  Background:
    When I am on "{url_register}{behat_secret_key}"
    And I wait for the page to load

  Scenario: As an authenticated user, I should be able to sign in after providing valid credentials
    Given I fill in "edit-full-name" with "Nikita Jain"
    And I fill in "edit-mail" with "{spc_new_registered_user_email}"
    And I fill in "edit-pass" with "{spc_new_registered_user_password}"
    And I wait 5 seconds
    And I click on "#edit-submit" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on homepage
    And I am on "user/login"
    And I am logged in as an authenticated user "{spc_new_registered_user_email}" with password "{spc_new_registered_user_password}"
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/user" page
