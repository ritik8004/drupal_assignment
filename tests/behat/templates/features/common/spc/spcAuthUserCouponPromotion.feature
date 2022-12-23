@javascript @coupon-promotion @discount @smoke @auth @mckwuat @mcsauat @mcaeuat
Feature: SPC to add coupon promotions & get discount in cart page for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait for element ".totals .discount-total"
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @language @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    When I follow "{language_link}"
    And I wait for element "#block-page-title"
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait for element ".totals .discount-total"
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"
    Then the element ".discount-total" should exist

  @mobile @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait for element ".totals .discount-total"
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist
