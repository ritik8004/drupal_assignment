@javascript @promotions @free-gifts @smoke @auth
Feature: SPC to checkout promotions (Free Gifts) on PDP and cart page without coupon for Authenticated User

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop
  Scenario: As an Authenticated user, I should be able to checkout promotions on PDP and cart page without coupon
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait for element "span.show-less-link"
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait for element ".ui-dialog-titlebar-close"
    And I click on ".ui-dialog-titlebar-close" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    #-Product quantity loader
    And I wait 2 seconds
    Then I should see "2"
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language
  Scenario: As an Authenticated user, I should be able to checkout promotions on PDP and cart page without coupon in second language
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait for AJAX to finish
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait for element ".ui-dialog-titlebar-close"
    And I click on ".ui-dialog-titlebar-close" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    #-Product quantity loader
    And I wait 2 seconds
    Then I should see "2"
    And I wait for AJAX to finish
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I wait for AJAX to finish
    Then I should see "{language_order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"

  @language @mobile
  Scenario: As an Authenticated user, I should be able to checkout promotions on mobile for PDP and cart page without coupon (mobile)
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait for AJAX to finish
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait for element ".ui-dialog-titlebar-close"
    And I click on ".ui-dialog-titlebar-close" element
    And I wait for AJAX to finish
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    #-Product quantity loader
    And I wait 2 seconds
    Then I should see "2"
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I wait for AJAX to finish
    Then I should see "{language_order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"
    