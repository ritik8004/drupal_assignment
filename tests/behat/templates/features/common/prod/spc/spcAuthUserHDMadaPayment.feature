@javascript @auth @madaPayment @homeDelivery
Feature: SPC Checkout Home Delivery MADA Card Payment using CC for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @cc @hd @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) with MADA Cards (VISA Card)
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
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @hd @language @desktop @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
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
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @hd @language @mobile @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
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
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @hd @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) with MADA Cards (Mastercard Card)
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
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @hd @language @desktop @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
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
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @hd @language @mobile @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
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
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I click jQuery "#authsubmit" element on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element
