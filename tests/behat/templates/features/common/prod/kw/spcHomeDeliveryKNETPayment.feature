@javascript @KNetPayment @checkoutPayment @homeDelivery @auth @coskwpprod @tbskwpprod @coskwprod @pbkkwpprod @westelmkwpprod @vskwpprod @pbkkwprod @bpkwprod @westelmkwprod @hmkwprod @mckwprod @bbwkwpprod @flkwprod @pbkwprod @bbwkwprod @tbskwprod @vskwprod @westelmkwprod
Feature: SPC Checkout Home Delivery of KNET payment for Guest User

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @hd @Knet
  Scenario: As a Guest, I should be able to checkout using KNET payment method
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
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I select the Knet payment method
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "Eidity [KNET]" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "Submit"
    And I wait 2 seconds
    And I press "Cancel"
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should be on "/checkout"
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @desktop @Knet
  Scenario: As a Guest, I should be able to checkout using KNET payment method in second language
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
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I select the Knet payment method
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "إرسال"
    And I wait 2 seconds
    And I press "الغاء"
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @mobile @Knet
  Scenario: As a Guest, I should be able to checkout using KNET payment method in second language
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
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I select the Knet payment method
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "إرسال"
    And I wait 2 seconds
    And I press "الغاء"
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should be on "/checkout" page
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
