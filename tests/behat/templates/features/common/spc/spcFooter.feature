@javascript @smoke @footer @pbkkwuat @mujikwuat @cosaeuat @coskwuat @mujisauat @mujiaeuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @hmaeuat @mckwuat @vsaeuat @tbskwuat @vssauat @flkwuat @bbwkwuat @hmkwuat @hmsauat @mcsauat @mcaeuat @vskwuat @vsaeuat @flkwuat @flsauat @flaeuat @bbwsauat @bbwaeuat
Feature: Test Footer on the site

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"
    Then I scroll to the ".region__highlighted " element
    And I wait 2 seconds

  @desktop @footer
  Scenario: As a Guest, I should be able to see the footer
    And I scroll to the ".c-footer" element
    And the element ".c-footer-primary" should exist
    And the element ".c-footer-secondary" should exist
    And the element "#block-aboutbrand" should exist
    And the element "#block-alshaya-help" should exist
    And the element "#block-sociallinks" should exist
    And the element "#edit-email" should exist
    And I fill in an element having class "#edit-email" with "test123@user.com"
    And I press "edit-newsletter"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see an "#footer-newsletter-form-wrapper" element
    And the element ".c-footer-secondary" should exist
    And the element "#block-copyright" should exist

  @desktop @footer @language
  Scenario: As a Guest, I should be able to see the footer
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".c-footer" element
    And the element ".c-footer-primary" should exist
    And the element ".c-footer-secondary" should exist
    And the element "#block-aboutbrand" should exist
    And the element "#block-alshaya-help" should exist
    And the element "#block-sociallinks" should exist
    And the element "#edit-email" should exist
    And I fill in an element having class "#edit-email" with "test12@user.com"
    And I press "edit-newsletter"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see an "#footer-newsletter-form-wrapper" element
    And the element ".c-footer-secondary" should exist
    And the element "#block-copyright" should exist

  @language @mobile
  Scenario: As a Guest, I should be able to remove products from the basket in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 5 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I click on Add-to-cart button
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And I scroll to the ".c-footer" element
    And the element ".c-footer-primary" should exist
    And the element ".c-footer-secondary" should exist
    And the element "#block-aboutbrand" should exist
    And the element "#block-alshaya-help" should exist
    And the element "#block-sociallinks" should exist
    And the element "#edit-email" should exist
    And I fill in an element having class "#edit-email" with "test12@user.com"
    And I press "edit-newsletter"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see an "#footer-newsletter-form-wrapper" element
    And the element ".c-footer-secondary" should exist
    And the element "#block-copyright" should exist
