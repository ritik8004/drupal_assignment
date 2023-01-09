@javascript @dynamic-promo @discount @smoke @auth @mckwuat @mcsauat @mcaeuat
Feature: SPC to add dynamic promotions (Add 2 more to get x% discount) for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @dynamic @add-3-more
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Add 3 more to get x% discount) on cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element ".promotions-full-view-mode" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    And I wait for element ".totals .discount-total"
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element "#delivery-method-home_delivery"
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
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @desktop @language @dynamic @add-3-more
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Add 3 more to get x% discount) on cart page for second language
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    When I follow "{language_link}"
    And I wait for the page to load
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element ".promotions-full-view-mode" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element "#delivery-method-home_delivery"
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
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @mobile @language @dynamic @add-3-more
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Add 3 more to get x% discount) on cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for element "#block-page-title"
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element ".promotions-full-view-mode" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element "#delivery-method-home_delivery"
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
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist
