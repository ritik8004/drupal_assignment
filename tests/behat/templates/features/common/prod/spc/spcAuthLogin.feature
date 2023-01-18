@javascript @createaccount @smoke @auth @mujikwprod @mujisaprod @mujiaeprod @hmaeprod @hmsaprod @hmkwprod @vsaeprod @vssaprod @vskwprod @aeoaeprod @aeosaprod @aeokwprod @bbwaeprod @bbwsaprod @bbwkwprod @bpaeprod @bpsaprod @bpkwprod @cosaeprod @cossaprod @coskwprod @flaeprod @flsaprod @flkwprod @mcaeprod @mcsaprod @mckwprod @pbaeprod @pbsaprod @pbkwprod @pbksaprod @pbkaeprod @pbkkwprod @tbsegprod @tbskwprod @westelmkwprod @westelmsaprod @westelmaeprod
@mckwpprod @mcaepprod @mcsapprod @pbaepprod @pbkwpprod @pbsapprod @bbwaepprod @bbwsapprod @bbwkwpprod @flaepprod @flsapprod @flkwpprod @hmaepprod @hmsapprod @hmsapprod @vskwpprod @vsaepprod @vskwpprod @westelmkwpprod @westelmaepprod @westelmsapprod @pbkaepprod @pbksapprod @pbkkwpprod @mujiaepprod @mujikwpprod @bpaepprod @bpsapprod @bpkwpprod @tbskwpprod @aeoaepprod @aeokwpprod @aeosapprod
Feature: Create new user account on the site

  Background:
    Given I am on "/user/register"
    And I wait for element "#block-page-title"

  Scenario: As an authenticated user, I should be able to sign in after providing valid credentials
    Given I fill in "edit-full-name" with "Nikita Jain"
    And I create an account with "{spc_new_registered_user_email}" using custom password
    And I wait for the page to load
    And I uncheck the newsletter subscription checkbox
    And I click on "#edit-submit" element
    And I wait for the page to load
    Then I should be on homepage
    And I am on "user/login"
    And I wait for element "#block-page-title"
    And I login with "{spc_new_registered_user_email}" using custom password
    And I wait for element "#block-page-title"
    Then I should be on "/user" page
