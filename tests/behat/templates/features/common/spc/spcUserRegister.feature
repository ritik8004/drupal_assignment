@javascript @smoke @pbkkwuat @mujikwuat @coskwuat @cosaeuat @mujisauat @mujiaeuat @pbksauat @pbkaeuat @tbseguat @bpaeuat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @pbsauat @hmaeuat @mckwuat @vssauat @bbwkwuat @bbwsauat @mcsauat @mcaeuat @hmkwuat @flsauat @hmsauat @flaeuat @tbskwuat @bbwaeuat @vsaeuat @pbaeuat @pbkwuat
Feature: Test the User Registration functionality

  Background:
    Given I am on "user/register"
    And I wait for element "#block-page-title"

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
