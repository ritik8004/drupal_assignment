@javascript @auth @checkoutPayment @Tabby @homeDelivery @vsaeuat @vssauat @bpsauat @flaeuat @flsauat @hmsauat @bbwaeuat @bbwsauat
Feature: SPC Checkout Home Delivery of Tabby payment for Guest user

  Background:
    Given I am on "{spc_pdp_page}"
    And I wait for element ".content__sidebar"

  @hd @tabby
  Scenario: As a Guest user, I should be able to checkout using Tabby payment method
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "checkout as guest"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-tabby" element on page
    And I wait for element "#tabby-card-checkout"
    And I wait for AJAX to finish
    Then the element "#tabby-card-checkout" should exist
    And I click on ".checkout-link.submit" element
    And I wait for AJAX to finish
    And I wait for element "#tabby-checkout"
    Then I should see tabby payment window

  @language @tabby
  Scenario: As a Guest user, I should be able to checkout using Tabby payment method for second language
    When I follow "{language_link}"
    And I wait for element ".content__sidebar"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "checkout as guest"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-tabby" element on page
    And I wait for AJAX to finish
    Then the element "#tabby-card-checkout" should exist
    And I click on ".checkout-link.submit" element
    And I wait for AJAX to finish
    And I wait for element "#tabby-checkout"
    Then I should see tabby payment window
