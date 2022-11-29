@javascript @KNetPayment @homeDelivery @checkoutPayment @auth @coskwpprod @tbskwpprod @coskwprod @pbkkwpprod @westelmkwpprod @vskwpprod @pbkkwprod @bpkwprod @westelmkwprod @hmkwprod @mckwprod @bbwkwprod @flkwprod @pbkwprod @tbskwprod @vskwprod @westelmkwprod
Feature: SPC Checkout Home Delivery Knet Payment for Authenticated User

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @hd @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET payment method
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
    And I select the Knet payment method
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "Eidity [KNET]" from dropdown ".paymentselect"
    And I wait 5 seconds
    And I press "proceed"
    And I wait 5 seconds
    And I press "cancel"
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should be on "/checkout"
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @desktop @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET payment method in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
    And I select the Knet payment method
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 10 seconds
    And I press "إرسال"
    And I wait 10 seconds
    And I press "الغاء"
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @hd @language @mobile @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET payment method in second language
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
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
