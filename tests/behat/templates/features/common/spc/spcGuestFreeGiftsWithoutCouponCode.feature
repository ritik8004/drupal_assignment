@javascript @promotions @free-gifts @smoke @guest @mcsauat @mckwuat @mcaeuat
Feature: SPC to checkout promotions (Free Gifts) on PDP and cart page without coupon for Guest User

  @desktop
  Scenario: As a Guest, I should be able to checkout promotions on PDP and cart page without coupon
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait 5 seconds
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait 5 seconds
    And I click on ".ui-dialog-titlebar-close" element
    And I wait 5 seconds
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/cart"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled
    And I wait 10 seconds
    And I wait for the page to load
    And I should see "(3 items)" in the "#block-content .spc-content .spc-checkout-section-title" element
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait for the page to load
    And I wait 10 seconds
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I wait 5 seconds
    And I wait for the page to load
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I wait 10 seconds
    Then I should see "{order_summary}"
    And I wait 10 seconds
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @language
  Scenario: As a Guest, I should be able to checkout promotions on PDP and cart page without coupon in second language
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait 5 seconds
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait 5 seconds
    And I click on ".ui-dialog-titlebar-close" element
    And I wait 5 seconds
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 30 seconds
    And I wait for the page to load
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{language_order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"

  @language @mobile
  Scenario: As a Guest, I should be able to checkout promotions on mobile for PDP and cart page without coupon (mobile)
    Given I am on "{spc_single_product_detail_page_no_coupon}"
    And I wait for the page to load
    And the element "#block-content .free-gift-promotions" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-image" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-title" should exist
    And the element "#block-content .free-gift-promotions .free-gift-promo-list .free-gift-message" should exist
    When I click on ".free-gift-message a" element
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see an ".content--short-description" element
    And I click on "span.read-more-description-link-gift" element
    And I wait 5 seconds
    Then I should see an "span.show-less-link" element
    And I click on "span.show-less-link" element
    And I wait 5 seconds
    And I click on ".ui-dialog-titlebar-close" element
    And I wait 5 seconds
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/cart"
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-cart-item .spc-product-tile-actions .qty .spcSelect__control--is-disabled" should exist
    And the element ".spc-content .spc-cart-items .spc-cart-item:nth-child(2) .spc-product-tile" should exist
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "#block-content .spc-main .spc-content .spc-cart-items .freegift-label" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait for the page to load
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    Then I should see "{language_order_summary}"
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com_upapi" element on page
    And I wait for AJAX to finish
    Then the "payment-method-checkout_com_upapi" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com_upapi .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{language_order_detail}"
