@javascript @guest @checkoutPayment @homeDelivery @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbksapprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @westelmsapprod @westelmkwpprod @westelmaepprod @vskwpprod @pbkaeprod @pbksaprod @pbkkwprod @mujiaeprod @mujisaprod @mujikwprod @bpkwprod @tbsegprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @westelmkwprod @westelmaeprod @westelmsaprod @vskwprod @vssapprod @vsaepprod @pbsapprod @pbaepprod @pbkwpprod @bbwsapprod @bbwaepprod @bbwkwpprod @hmsapprod @hmkwpprod @hmaepprod @flsapprod @flaepprod @flkwpprod @mcsapprod @mckwprod @vssaprod @vsaeprod @pbsaprod @pbaeprod @pbkwprod @bbwsaprod @bbwaeprod @bbwkwprod @hmsaprod @hmkwprod @hmaeprod @flsaprod @flaeprod @flkwprod @mcsaprod @mckwprod
Feature: SPC Checkout Home Delivery using checkout_com method (2D cards)

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cc @hd @checkout_com
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @desktop @checkout_com
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait 10 seconds
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @mobile @checkout_com
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
