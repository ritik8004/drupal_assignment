@javascript
Feature: Test the User Registeration functionality

  Scenario: Anonymous user should be able to see correct fields on user register page
    Given I am on "user/register"
    And I wait 10 seconds
    Then I should see "{create_account}"
    And the element ".c-content__region .region__content #block-content #user-register-form" should exist
    And the element "#user-register-form #edit-field-first-name-wrapper" should exist
    And the element "#user-register-form #edit-field-last-name-0-value" should exist
    And the element "#user-register-form #edit-mail" should exist
    And the element "#user-register-form #edit-pass" should exist
    And the element "#user-register-form .captcha" should exist
    And the element "#user-register-form #edit-field-subscribe-newsletter-value" should exist
    And the element "#user-register-form #edit-actions #edit-submit" should exist
    And the element ".c-content__region .region__content #block-alshayasocialloginblock" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_facebook" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_google" should exist
    And the element ".c-content__region .region__content #block-alshayasignupsigninbuttonsblock" should exist
