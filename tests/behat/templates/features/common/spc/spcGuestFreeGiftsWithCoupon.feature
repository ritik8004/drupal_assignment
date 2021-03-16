@javascript @promotions @free-gifts @smoke @guest @bbwaeuat
Feature: SPC to checkout promotions (Free Gifts) on PDP page with coupon-code for Guest User

  @desktop @test
  Scenario: As a Guest, I should be able to checkout promotions on PDP page with coupon-code for multiple products
    Given I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    Then I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/cart"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    And I wait 5 seconds
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I press "{add_free_gift}"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language @desktop
  Scenario: As a Guest, I should be able to checkout promotions on PDP page with coupon-code for multiple products in second language
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
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/{language_short}/cart"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    And I wait 5 seconds
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I press "{add_free_gift}"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait for the page to load
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language @mobile
  Scenario: As a Guest, I should be able to checkout promotions on mobile for PDP page with coupon-code (mobile)
    Given I am on "{spc_multiple_product_detail_page_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    And the element "#block-content .free-gift-promotions .free-gift-coupon-code" should exist
    Then I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/cart"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo" should exist
    When I click on "#block-content .spc-main .spc-content .spc-cart-items .free-gift-promo .coupon-code" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should see a "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(2)" element on page
    And I wait 5 seconds
    And I click on "#drupal-modal.free-gift-listing-modal .item-list .slick-track li:nth-child(1) a" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I press "{add_free_gift}"
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    Then the promo code should be applied
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
