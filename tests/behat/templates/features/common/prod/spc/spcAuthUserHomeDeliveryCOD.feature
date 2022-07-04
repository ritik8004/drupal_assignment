@javascript @auth @codPayment @homeDelivery
Feature: SPC Checkout Home Delivery COD for Authenticated Users

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 5 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 5 seconds

  @cod @hd
  Scenario: As a Authenticated User, I should be able to checkout using COD
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then the element ".delivery-information-preview" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    Then the "payment-method-cashondelivery" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cod @hd @language @desktop
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait 5 seconds
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then the element ".delivery-information-preview" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    Then the "payment-method-cashondelivery" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cod @hd @language @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock #cart_notification div.matchback-cart-notification-close" element
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then the element ".delivery-information-preview" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    Then the "payment-method-cashondelivery" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cod @hd @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD (mobile)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock #cart_notification div.matchback-cart-notification-close" element
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then the element ".delivery-information-preview" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    Then the "payment-method-cashondelivery" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
