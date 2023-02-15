@javascript @account @smoke @auth @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbksapprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @westelmkwpprod @westelmaepprod @westelmsapprod @pbkkwprod @pbksaprod @pbkaeprod @mujiaeprod @mujisaprod @mujikwprod @tbsegprod @bpkwprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @westelmkwprod @westelmaeprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @mcaepprod @tbskwprod @mckwprod @vskwprod @mckwpprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @flaeprod @flkwprod @flsaprod @flaepprod @flkwpprod @flsapprod @hmaeprod @vskwpprod @hmkwprod @hmsaprod
@hmaepprod @hmkwpprod @hmsapprod @vsaeprod @vssaprod @vsaepprod @vssapprod @pbaeprod @pbkwprod @pbsaprod @pbaepprod @pbkwpprod @pbsapprod @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod
Feature: Add address to new user account.

  Background:
    Given I am on "user/login"
    And I wait for element "#block-page-title"
    And I login with "{spc_new_registered_user_email}" using custom password
    Then I press "edit-submit"
    And I wait for the page to load
    Then I should be on "/user" page

  Scenario: Authenticated user should be able to login into the system
    Then the element "#block-page-title .c-page-title" should exist
    And the element "#block-userrecentorders" should exist
    And the element "#block-userrecentorders .no--orders" should exist
    And the element "#block-userrecentorders .subtitle" should exist
    And the element "a.edit-account-btn-button" should exist
    And I should see "You have no recent orders to display."

  @address @desktop
  Scenario: As an authenticated user, I should be able to add address to my address book
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait for element "#block-page-title"
    Then I check the address-book form
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
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
    And I wait for element ".messages--status"
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

  @mobile
  Scenario: As an authenticated user, I should be able to add address to my address book for Mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait for element "#block-page-title"
    Then I check the address-book form
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    And I select "City" option from "field_address[0][address][area_parent]"
    And I wait for AJAX to finish
    And I select "Area" option from "field_address[0][address][administrative_area]"
    And I wait for AJAX to finish
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
    And I wait for element ".messages--status"
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist
