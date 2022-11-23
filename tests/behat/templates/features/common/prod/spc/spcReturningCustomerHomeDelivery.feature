@javascript @returnUser @codPayment @homeDelivery @tbsegprod @tbsegpprod
Feature: SPC Checkout Home Delivery COD for returning customer

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cod @hd
  Scenario: As a returning customer, I should be able to checkout using COD
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
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @cod @hd @language @desktop
  Scenario: As a returning customer, I should be able to checkout using COD in second language
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
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @cod @hd @language @mobile
  Scenario: As a returning customer, I should be able to checkout using COD in second language
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
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist
