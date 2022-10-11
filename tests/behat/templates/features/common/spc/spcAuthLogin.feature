@javascript @createaccount @smoke @auth @mujikwuat @coskwuat @mujisauat @cosaeuat @coskwuat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @pbsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @bpaeqa @tbskwuat @bbwsauat @mcsaqa @flsauat @hmaeuat @vskwqa @vsaeqa @flkwuat @hmkwqa @mckwuat @vsaeuat @vssauat @bbwkwuat @bbwaeuat @hmkwuat @hmsauat @mcsauat @mcaeuat @flaeuat @pbkwuat @pbsauat @pbaeuat
Feature: Create new user account on the site

  Background:
    When I am on user registration page
    And I wait for the page to load

  Scenario: As an authenticated user, I should be able to sign in after providing valid credentials
    Given I fill in "edit-full-name" with "Nikita Jain"
    And I create an account with "{spc_new_registered_user_email}" using custom password
    And I wait for the page to load
    And I uncheck the newsletter subscription checkbox
    And I click on "#edit-submit" element
    And I wait for the page to load
    Then I should be on homepage
    And I am on "user/login"
    And I wait for the page to load
    And I login with "{spc_new_registered_user_email}" using custom password
    And I wait for the page to load
    Then I should be on "/user" page
