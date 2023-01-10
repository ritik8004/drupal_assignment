@javascript @smoke @footer @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @pbkaepprod @coskwprod @cosaeprod @cossaprod @pbksapprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @pbkaeprod @bpaepprod @bpsapprod @bpkwpprod @vskwpprod @westelmsapprod @westelmkwpprod @westelmaepprod @pbksaprod @pbkkwprod @mujiaeprod @mujisaprod @mujikwprod @bpkwprod @tbsegprod @bpaeprod @bpsaprod @westelmkwprod @aeoaeprod @aeokwprod @aeosaprod @westelmaeprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @vskwprod @mcaepprod @tbskwprod @mckwprod @mckwpprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @flaeprod @flkwprod @flsaprod @flaepprod @flkwpprod @flsapprod @hmaeprod @hmkwprod @hmsaprod @hmaepprod @hmkwpprod @hmsapprod @vsaeprod @vssaprod @vsaepprod @vssapprod @pbaeprod @pbsaprod @pbaepprod @pbkwpprod
Feature: Test Footer on the site

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 2 seconds
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
    And I fill in an element having class "#edit-email" with "test111@user.com"
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
    And I fill in an element having class "#edit-email" with "test111@user.com"
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
    And I fill in an element having class "#edit-email" with "test111@user.com"
    And I press "edit-newsletter"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see an "#footer-newsletter-form-wrapper" element
    And the element ".c-footer-secondary" should exist
    And the element "#block-copyright" should exist

