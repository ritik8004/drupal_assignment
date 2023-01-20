@javascript @auth @exclusive-coupon @bbwkwuat
Feature: SPC to add Exclusive coupon & get discount in cart page for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @free-gift
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the Free Gift product
    When I am on "{spc_free_gift}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an ".freegift-label" element
    And the element ".totals .discount-total" should not exist
    When I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    Then I should see an "#exclusive-promo-message" element
    And I should not see an ".freegift-label" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @desktop @discount-price
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the discounted price for PDP
    When I am on "{spc_discounted_pdp_page}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an "div.spc-product-price .price-block div.has--special--price" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    Then I should not see an "div.spc-product-price .price-block div.has--special--price" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @desktop @bbw-exclusive
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon promotions & get discount directly on the Cart page
    When I go to in stock product page
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery"
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I click on "a.logo" element
    And I wait for element "#block-content"
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @desktop @bbwfixed
  Scenario: As an Authenticated User, I should be able to add Exclusive fixed amount coupon & get discount directly on the Cart page
    When I am on "{spc_single_product_detail_page_coupon}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "newoffer" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    Then I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element

  @desktop @bbw-dynamic
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code and the Dynamic promotion not be applicable
    When I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I should see an ".dynamic-promotion-wrapper" element
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I should not see an ".dynamic-promotion-wrapper" element
    And I click on "#promo-remove-button" element
    And I wait for AJAX to finish
    Then I should not see an ".totals .discount-total" element

  @desktop @buy2-get1-free
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code for buy 2 get 1 free products
    When I am on "{spc_promotion_pdp_page}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @language @free-gift
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the Free Gift product for Arabic
    When I am on "{spc_free_gift}"
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I should see an ".freegift-label" element
    And the element ".totals .discount-total" should not exist
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    Then I should not see an ".freegift-label" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @language @discount-price
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the discounted price for PDP for Arabic
    When I am on "{spc_discounted_pdp_page}"
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an "div.spc-product-price .price-block div.has--special--price" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    Then I should not see an "div.spc-product-price .price-block div.has--special--price" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @language @bbw-exclusive
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon promotions & get discount directly on the Cart page in Arabic
    When I go to in stock product page
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery"
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I click on "a.logo" element
    And I wait for element "#block-content"
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @language @bbwfixed
  Scenario: As an Authenticated User, I should be able to add Exclusive fixed amount coupon & get discount directly on the Cart page in Arabic
    When I am on "{spc_single_product_detail_page_coupon}"
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "newoffer" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    Then I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element

  @language @bbw-dynamic
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code and the Dynamic promotion not be applicable in Arabic
    When I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I should see an ".dynamic-promotion-wrapper" element
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I should not see an ".dynamic-promotion-wrapper" element
    And I click on "#promo-remove-button" element
    And I wait for AJAX to finish
    Then I should not see an ".totals .discount-total" element

  @language @buy2-get1-free
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code for buy 2 get 1 free products for Arabic
    When I am on "{spc_promotion_pdp_page}"
    And I wait for element "#block-content"
    When I follow "{language_link}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @mobile @free-gift
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the Free Gift product for Mobile
    When I am on "{spc_free_gift}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I should see an ".freegift-label" element
    And the element ".totals .discount-total" should not exist
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    Then I should not see an ".freegift-label" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @mobile @discount-price
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code on the discounted price for PDP for Mobile
    When I am on "{spc_discounted_pdp_page}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an "div.spc-product-price .price-block div.has--special--price" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    Then I should not see an "div.spc-product-price .price-block div.has--special--price" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @mobile @bbw-exclusive
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon promotions & get discount directly on the Cart page in Mobile
    When I go to in stock product page
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery"
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I click on "a.logo" element
    And I wait for element "#block-content"
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  @mobile @bbwfixed
  Scenario: As an Authenticated User, I should be able to add Exclusive fixed amount coupon & get discount directly on the Cart page in Mobile
    When I am on "{spc_single_product_detail_page_coupon}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "newoffer" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    Then I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element

  @mobile @bbw-dynamic
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code and the Dynamic promotion not be applicable in Mobile
    When I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    And I should see an ".dynamic-promotion-wrapper" element
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I should not see an ".dynamic-promotion-wrapper" element
    And I click on "#promo-remove-button" element
    And I wait for AJAX to finish
    Then I should not see an ".totals .discount-total" element

  @mobile @buy2-get1-free
  Scenario: As an Authenticated User, I should be able to add Exclusive coupon code for buy 2 get 1 free products for Mobile
    When I am on "{spc_promotion_pdp_page}"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 2 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".spc-promo-code-block" element
    And I should see an "#promo-code" element
    And I fill in "#promo-code" with "exclusive" using jQuery
    And I press "promo-action-button"
    And I wait for AJAX to finish
    And I wait for element "#exclusive-promo-message"
    Then the promo code should be applied
    And I should see an "#exclusive-promo-message" element
    And the element ".totals .discount-total" should exist
    And I should see an "div.total-line-item span.discount-total .tooltip-anchor div.applied-exclusive-couponcode" element
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for element "div.spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element
