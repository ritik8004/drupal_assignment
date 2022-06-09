@javascript @smoke @mujisapprod @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbksapprod @pbkkwpprod @mujiaepprod @mujikwpprod @pbkaeprod @aeoaepprod @aeokwpprod @aeosapprod @westelmkwpprod @bpaepprod @bpsapprod @bpkwpprod @westelmsapprod @westelmaepprod @pbksaprod @vskwpprod @pbkkwprod @bpkwprod @mujiaeprod @mujisaprod @mujikwprod @bpaeprod @tbsegprod @bpsaprod @westelmkwprod @westelmaeprod @aeoaeprod @aeokwprod @aeosaprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @vskwprod @mcaepprod @tbskwprod @mckwprod @mckwpprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @flaeprod @flkwprod @flsaprod @flaepprod @flkwpprod @flsapprod @hmaeprod @hmkwprod @hmsaprod @hmaepprod @hmkwpprod @hmsapprod @vsaeprod @vssaprod @vsaepprod @vssapprod @pbaeprod @pbkwprod @pbsaprod @pbaepprod @pbkwpprod @pbsapprod
Feature: Test MiniCart page

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for AJAX to finish

  Scenario: As a Guest, I should be able minicart
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/cart"

  @desktop
  Scenario: As a Guest, I should be able add content in minicart
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/cart"

  @desktop @language
  Scenario: As a Guest, I should be able minicart in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/{language_short}/cart" page

  @desktop @language
  Scenario: As a Guest, I should be able add content in minicart in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/{language_short}/cart" page

  @mobile @language
  Scenario: As a Guest, I should be able minicart in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/{language_short}/cart" page

  @mobile @language
  Scenario: As a Guest, I should be able add content in minicart in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted" element
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/{language_short}/cart" page

  @mobile
  Scenario: As a Guest, I should be able add content in minicart
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-1 img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/cart"
