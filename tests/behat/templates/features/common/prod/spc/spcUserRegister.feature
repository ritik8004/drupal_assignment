@javascript @smoke @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @pbkaepprod @pbksapprod @coskwprod @cosaeprod @cossaprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @pbkaeprod @pbksaprod @westelmsapprod @westelmkwpprod @westelmaepprod @vskwpprod @pbkkwprod @mujiaeprod @mujisaprod @mujikwprod @bpkwprod @bpaeprod @bpsaprod @tbsegprod @aeoaeprod @aeokwprod @aeosaprod @westelmkwprod @westelmaeprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @vskwprod @mcaepprod @mckwprod @mckwpprod @tbskwprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @flaeprod @flkwprod @flsaprod @flaepprod @flkwpprod @flsapprod @hmaeprod @hmkwprod @hmsaprod @hmaepprod @hmkwpprod @hmsapprod @vsaeprod @vssaprod @vsaepprod @vssapprod @pbaeprod @pbkwprod @pbsaprod @pbaepprod @pbkwpprod @pbsapprod
Feature: Test the User Registeration functionality

  Background:
    Given I am on "user/register"
    And I wait 10 seconds

  Scenario: Anonymous user should be able to see correct fields on user register page
    Then I should see "{create_account}"
    And the element ".c-content__region .region__content #block-content #user-register-form" should exist
    And the element "#user-register-form #edit-full-name" should exist
    And the element "#user-register-form #edit-mail" should exist
    And the element "#user-register-form #edit-pass" should exist
    And the element "#user-register-form #edit-field-subscribe-newsletter-value" should exist
    And the element "#user-register-form #edit-actions #edit-submit" should exist
    And the element ".c-content__region .region__content #block-alshayasocialloginblock" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_facebook" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_google" should exist
    And the element ".c-content__region .region__content #block-alshayasignupsigninbuttonsblock" should exist

  @language @desktop
  Scenario: Anonymous user should be able to see correct fields on user register page in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element ".c-content__region .region__content #block-content #user-register-form" should exist
    And the element "#user-register-form #edit-full-name" should exist
    And the element "#user-register-form #edit-mail" should exist
    And the element "#user-register-form #edit-pass" should exist
    And the element "#user-register-form #edit-field-subscribe-newsletter-value" should exist
    And the element "#user-register-form #edit-actions #edit-submit" should exist
    And the element ".c-content__region .region__content #block-alshayasocialloginblock" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_facebook" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_google" should exist
    And the element ".c-content__region .region__content #block-alshayasignupsigninbuttonsblock" should exist

  @language @mobile
  Scenario: Anonymous user should be able to see correct fields on user register page in second language (Mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element ".c-content__region .region__content #block-content #user-register-form" should exist
    And the element "#user-register-form #edit-full-name" should exist
    And the element "#user-register-form #edit-mail" should exist
    And the element "#user-register-form #edit-pass" should exist
    And the element "#user-register-form #edit-field-subscribe-newsletter-value" should exist
    And the element "#user-register-form #edit-actions #edit-submit" should exist
    And the element ".c-content__region .region__content #block-alshayasocialloginblock" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_facebook" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_google" should exist
    And the element ".c-content__region .region__content #block-alshayasignupsigninbuttonsblock" should exist
