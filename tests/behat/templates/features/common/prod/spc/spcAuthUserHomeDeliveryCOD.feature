@javascript @auth @codPayment @homeDelivery @tbsegprod @tbsegpprod
Feature: SPC Checkout Home Delivery COD for Authenticated Users

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @cod @hd @desktop
  Scenario: As a Authenticated User, I should be able to checkout using COD
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait for element "input#payment-method-cashondelivery[checked]"
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @cod @hd @language @desktop
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait for element "input#payment-method-cashondelivery[checked]"
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @cod @hd @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD (mobile)
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait for element "input#payment-method-cashondelivery[checked]"
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @cod @hd @language @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language for mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait for element "input#payment-method-cashondelivery[checked]"
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist
