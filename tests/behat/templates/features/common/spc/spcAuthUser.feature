@javascript @account @smoke @auth @pbsauat @hmaeuat @flkwuat @mckwuat @vssauat @bbwkwuat @hmkwuat @hmsauat @mcsauat @mcaeuat @flaeuat @pbkwuat @pbsauat @pbaeuat
Feature: Test the My Account functionality

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_new_registered_user_email}"
    And I fill in "edit-pass" with "{spc_new_registered_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page

  Scenario: Authenticated user should be able to login into the system
    Then the element "#block-page-title .c-page-title" should exist
    And the element "#block-userrecentorders" should exist
    And the element "#block-userrecentorders .no--orders" should exist
    And the element "#block-userrecentorders .subtitle" should exist
    And the element "#block-userrecentorders .edit-account" should exist
    And I should see "You have no recent orders to display."

  @address
  Scenario: As an authenticated user, I should be able to address to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li > a.my-account-address-book"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I check the address-book form
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    Then I select "{city_option}" from "field_address[0][address][area_parent]" address
    And I wait 2 seconds
    Then I select "{governorate}" from "field_address[0][address][area_parent]" address
    And I wait 2 seconds
    Then I select "{address_area_field}" from "field_address[0][address][administrative_area]" address
    When I scroll to the ".country-field-wrapper" element
    When fill in billing address with following:
      | field_address[0][address][address_line1]             | {street}      |
      | field_address[0][address][dependent_locality]        | {building}    |
      | field_address[0][address][locality]                  | {locality}    |
      | field_address[0][address][address_line2]             | {floor}       |
    And I press "op"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist
    