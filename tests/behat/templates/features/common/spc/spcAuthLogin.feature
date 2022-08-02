@javascript @createaccount @smoke @auth @mujikwuat @coskwuat @mujisauat @cosaeuat @coskwuat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @pbsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @bpaeqa @tbskwuat @bbwsauat @mcsaqa @flsauat @hmaeuat @vskwqa @vsaeqa @flkwuat @hmkwqa @mckwuat @vsaeuat @vssauat @bbwkwuat @bbwaeuat @hmkwuat @hmsauat @mcsauat @mcaeuat @flaeuat @pbkwuat @pbsauat @pbaeuat
Feature: Create new user account on the site

  Background:
    When I am on "{url_register}{behat_secret_key}"
    And I wait 10 seconds
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
