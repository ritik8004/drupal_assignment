@javascript @pudo @auth @clickCollect @flsaprod
Feature: SPC Checkout using PUDO testing on the site for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_product_listing_page}"
    And I wait for element "#block-page-title"

  @cc @cnc @pudo
  Scenario: As an authenticated user, I should be able to check PUDO testing feature
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
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I should see an ".pickup-point-title" element
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @cnc @language @desktop @pudo
  Scenario: As a authenticated user, I should be able to check PUDO testing feature on second language
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
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I should see an ".pickup-point-title" element
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @cnc @language @mobile @pudo
  Scenario: As a authenticated user, I should be able to check PUDO testing on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for AJAX to finish
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
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I should see an ".pickup-point-title" element
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element
