@javascript @account @smoke @auth @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbksapprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @westelmkwpprod @westelmaepprod @westelmsapprod @pbkkwprod @pbksaprod @pbkaeprod @mujiaeprod @mujisaprod @mujikwprod @tbsegprod @bpkwprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @westelmkwprod @westelmaeprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @vskwprod @mcaepprod @tbskwprod @mckwprod @mckwpprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @vskwpprod @vssapprod @bbwkwpprod @mcaepprod @hmkwpprod @vssaprod @vssapprod @vsaeprod @vsaepprod @vsaepprod @pbaeprod @pbaepprod @pbaepprod @pbsaprod @pbsapprod @pbkwprod @pbkwpprod @mcaeprod @mcaepprod @mcsaprod @mcsapprod @mckwprod @mckwpprod @hmkwprod @hmkwpprod @flkwprod @flkwpprod @hmaeprod @hmaepprod @hmaepprod @flaeprod @flaepprod @flaepprod @hmsaprod @hmsapprod @flsaprod @flsapprod
Feature: Test the adding address to existing user account

  Background:
    Given I am on "user/login"
    And I wait for element "#block-page-title"
    And I login with "{spc_new_registered_user_email}" using custom password
    Then I press "edit-submit"
    And I wait for element "#block-page-title"

  Scenario: As an authenticated user, I should be able to add a new address to my address book
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait for element "#block-page-title"
    Then I click on "#block-content a" element
    And I wait for element "#block-page-title"
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
    And I wait for element ".messages--status"
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist
