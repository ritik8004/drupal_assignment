@javascript @free-shipping @promotion @smoke
Feature: SPC to add Free shipping promotion on cart for Guest user

  @desktop @guest @free-shipping @promotion
  Scenario: As a Guest User, I should be able to add Free shipping promotion of product on cart
    Given I am on "{spc_promotion_listing_page}"
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content #spc-cart .spc-pre-content .dynamic-promotion-wrapper div.inactive-promotions" should exist
    And I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for the product quantity loader
    Then I should see "3"
    And I wait for AJAX to finish
    And the element ".total-line-item .delivery-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
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
    Then the element ".checkout-link.submit" should exist

  @language @guest @free-shipping @promotion
  Scenario: As a Guest User, I should be able to add Free shipping promotion of product on cart for second language
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element ".promotions-full-view-mode" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    And I wait for the product quantity loader
    And I wait for element ".totals .discount-total"
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
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
    Then the element ".checkout-link.submit" should exist

  @mobile @guest @free-shipping @promotion
  Scenario: As a Guest User, I should be able to add Free shipping promotion of product on cart
    Given I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And the element ".promotions-full-view-mode" should exist
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait for AJAX to finish
    And I wait for element ".spc-product-tile-actions .spc-select .spcSelect__control"
    And I wait for AJAX to finish
    And I wait for the product quantity loader
    And I wait for element ".totals .discount-total"
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
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
    Then the element ".checkout-link.submit" should exist
