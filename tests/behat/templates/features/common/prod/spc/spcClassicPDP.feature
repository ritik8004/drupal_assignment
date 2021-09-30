@javascript @guest @pdp @homeDelivery @westelmaepprod @westelmsapprod @westelmkwpprod @vskwpprod @vssapprod @vsaepprod @pbkkwprod @pbksaprod @pbkaeprod @mujiaeprod @mujisaprod @mujikwprod @tbsegprod @bbwaeprod @pbsaprod @bbwkwprod @vskwprod @bbwsaprod @vssaprod @vsaeprod @pbaeprod @pbsaprod @pbkwprod
Feature: SPC Classic PDP block for desktop

  Background:
    Given I am on "{spc_pdp_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @pdp @desktop
  Scenario: To verify user is able to see product details on the PDP page
    And the element ".img-wrap" should exist
    And the element ".content__title_wrapper" should exist
    And the element ".price-type__wrapper .price div.price" should exist
    And the element ".edit-quantity" should exist
    And the element ".edit-add-to-cart" should exist
    And I click on Add-to-cart button
    And I wait 5 seconds
    And the element ".content--short-description" should exist
    And I click on ".read-more-description-link" element
    And I wait 5 seconds
    Then the element ".desc-open" should exist
    And I click on ".desc-open span.close" element
    And I wait 2 seconds
    Then the element ".desc-open" should not exist
    And the element ".delivery-options-wrapper" should exist
    And the element "#pdp-home-delivery" should exist
    And I click on "#pdp-home-delivery" element
    Then the element "#pdp-home-delivery .ui-accordion-header" should exist
    And the element "#pdp-stores-container" should exist
    And the element ".sharethis-wrapper" should exist

  @pdp @language
  Scenario: To verify user is able to see product details on the PDP page for second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element ".img-wrap" should exist
    And the element ".content__title_wrapper" should exist
    And the element ".price-type__wrapper .price div.price" should exist
    And the element ".edit-quantity" should exist
    And the element ".edit-add-to-cart" should exist
    And I click on Add-to-cart button
    And I wait 5 seconds
    And the element ".content--short-description" should exist
    And I click on ".read-more-description-link" element
    And I wait 5 seconds
    Then the element ".desc-open" should exist
    And I click on ".desc-open span.close" element
    And I wait 2 seconds
    Then the element ".desc-open" should not exist
    And the element ".delivery-options-wrapper" should exist
    And the element "#pdp-home-delivery" should exist
    And I click on "#pdp-home-delivery" element
    Then the element "#pdp-home-delivery .ui-accordion-header" should exist
    And the element "#pdp-stores-container" should exist
    And the element ".sharethis-wrapper" should exist
