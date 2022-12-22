@javascript @promotions @free-gifts @smoke @auth @wesauat
Feature: SPC to checkout promotions (Free Gifts) on PDP page and cart with coupon-code for Authenticated User for single and multiple products

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @single_product
  Scenario: As a Guest, I should be able to checkout promotions on PDP and cart page with coupon-code for single product
    Given I am on "{spc_single_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    And I should see an "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .gift-message" element
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    #-Verify quantity dropdown is disabled
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    #-Verify deleting the free gift item
    And I click on ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile .spc-product-tile-actions .spc-remove-btn " element
    And I wait for AJAX to finish
    Then the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2)" should not exist
    #-Verify gift coupon-code is visible
    And I should see an "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .gift-message" element
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    #-Verify remove the coupon-code, free gift is also removed
    And I click on "#promo-remove-button" element
    And I wait for AJAX to finish
    Then the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2)" should not exist
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{order_summary}"
    And the element ".spc-cart-item-alerts .freegift-label" should exist
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"


  @language @single_product
  Scenario: As a Guest, I should be able to checkout promotions on PDP and cart page with coupon-code for single product in second language
    Given I am on "{spc_single_product_detail_page_coupon}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    Then I press "{language_add_to_cart_link}"
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    And I should see an "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .gift-message" element
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    #-Verify quantity dropdown is disabled
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    #-Verify deleting the free gift item
    And I click on ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile .spc-product-tile-actions .spc-remove-btn " element
    And I wait for AJAX to finish
    Then the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2)" should not exist
    #-Verify gift coupon-code is visible
    And I should see an "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .gift-message" element
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    #-Verify remove the coupon-code, free gift is also removed
    And I click on "#promo-remove-button" element
    And I wait for AJAX to finish
    Then the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2)" should not exist
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{language_order_summary}"
    And the element ".spc-cart-item-alerts .freegift-label" should exist
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"

  @desktop @multiple_products
  Scenario: As an Authenticated user, I should be able to checkout promotions on PDP and cart page with coupon-code for multiple products
    Given I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And I press "{add_free_gift}"
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    Then I should see "{order_summary}"
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language @desktop
  Scenario: As an Authenticated user, I should be able to checkout promotions on PDP and cart page with coupon-code for multiple products in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And I press "{add_free_gift}"
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{order_summary}"
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language @mobile
  Scenario: As an Authenticated user, I should be able to checkout promotions on mobile for PDP and cart page with coupon-code (mobile)
    Given I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait for AJAX to finish
    And I press "{add_free_gift}"
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{order_summary}"
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
