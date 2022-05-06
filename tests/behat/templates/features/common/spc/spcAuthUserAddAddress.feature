@javascript @account @smoke @auth @mujikwuat @coskwuat @mujisauat @cosaeuat @coskwuat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @pbsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @hmaeuat @hmeguat @bbwsauat @flsauat @hmkwqa @tbskwuat @mckwuat @vssauat @vsaeuat @bbwkwuat @hmkwuat @bbwaeuat @flkwuat @hmsauat @mcsauat @mcaeuat @flaeuat @pbkwuat @pbsauat @pbaeuat
Feature: Test the adding address to existing user account

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page

  Scenario: As an authenticated user, I should be able to add a new address to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li > a.my-account-address-book"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-content a" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I check the address-book form
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    Then I scroll to the "#address-book-form-open" element
    And I select "City" option from "field_address[0][address][area_parent]"
    And I wait 5 seconds
    And I select "Area" option from "field_address[0][address][administrative_area]"
    And I wait 5 seconds
    When I scroll to the ".country-field-wrapper" element
    When fill in billing address with following:
      | field_address[0][address][address_line1]             | {street}      |
      | field_address[0][address][dependent_locality]        | {building}    |
      | field_address[0][address][locality]                  | {locality}    |
      | field_address[0][address][address_line2]             | {floor}       |
      | field_address[0][address][sorting_code]              | {landmark}    |
      | field_address[0][address][postal_code]               | {postal_code} |
    And I press "op"
    When I wait for AJAX to finish
    And I wait 10 seconds
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

