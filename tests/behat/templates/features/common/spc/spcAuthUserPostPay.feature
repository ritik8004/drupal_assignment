@javascript @auth @PostPay @homeDelivery @pbaeuat @bbwaeuat @flaeuat
Feature: SPC Checkout Home Delivery using Installments with PostPay method for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
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
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And the element "div.block-content .postpay" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#payment-method-postpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I should see an "div.postpay-widget" element
    And I should see an "div.postpay-payment-summary" element
    And I should see an "div.postpay-instalment-notice" element
#    Then I should see the link for "Continue with postpay"
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And the element "div#root" should exist
    Then the element "phone-number-input" should exist


    











