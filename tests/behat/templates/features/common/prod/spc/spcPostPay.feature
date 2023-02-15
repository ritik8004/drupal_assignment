@javascript @PostPay @homeDelivery @guest @vsaeprod @flaeprod @bpaeprod @mcaeprod @aeoaeprod @bbwaeprod
Feature: SPC Checkout Home Delivery using Installments with PostPay method for Guest user

  Background:
    Given I go to in stock category page
    And I wait for element "#block-content"

  @cc @hd @checkout_com
  Scenario: As a Guest user, I should be able to checkout using PostPay method
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element "div.postpay-widget" should exist
    And the element "div.postpay a" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "div.block-content .postpay" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-postpay" element on page
    And I wait for element "input#payment-method-postpay[checked]"
    Then the element "div.postpay-widget" should exist
    Then the element "div.postpay-payment-summary" should exist
    And I click on the checkout button
    Then I should see an iframe window
    And I wait for AJAX to finish
    Then I should see "Your postpay order has been cancelled"

  @language
  Scenario: As a Guest user, I should be able to checkout using PostPay method in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element "div.postpay-widget" should exist
    And the element "div.postpay a" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "div.block-content .postpay" should exist
    When I follow "إتمام الشراء بأمان"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-postpay" element on page
    And I wait for element "input#payment-method-postpay[checked]"
    Then the element "div.postpay-widget" should exist
    Then the element "div.postpay-payment-summary" should exist
    And I click on the checkout button
    Then I should see an iframe window
    And I wait for AJAX to finish
    Then I should see "طلبيتك من بوست باي تم إلغاءها"
