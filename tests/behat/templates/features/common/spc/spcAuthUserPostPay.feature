@javascript @auth @checkoutPayment @PostPay @homeDelivery @bbwaeuat @aeoaeuat @bpaeuat @vsaeuat @flaeuat @mcaeuat
Feature: SPC Checkout Home Delivery using Installments with PostPay method for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_basket_page}"
    And I wait for the page to load

  @cc @hd @checkout_com
  Scenario: As an Authenticated user, I should be able to checkout using PostPay method
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element "div.postpay-widget" should exist
    And the element "div.postpay a" should exist
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And the element "div.block-content .postpay" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-postpay" element on page
    And I wait 30 seconds
    And I wait for AJAX to finish
    Then the element "div.postpay-widget" should exist
    Then the element "div.postpay-payment-summary" should exist
    And I click on the checkout button
    Then I should see an iframe window
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should see "Your postpay order has been cancelled"

  @language
  Scenario: As an Authenticated user, I should be able to checkout using PostPay method in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element "div.postpay-widget" should exist
    And the element "div.postpay a" should exist
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And the element "div.block-content .postpay" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-postpay" element on page
    And I wait 30 seconds
    And I wait for AJAX to finish
    Then the element "div.postpay-widget" should exist
    Then the element "div.postpay-payment-summary" should exist
    And I click on the checkout button
    Then I should see an iframe window
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should see "طلبيتك من بوست باي تم إلغاءها"
