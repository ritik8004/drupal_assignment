@javascript @dynamic-promotion @discount @smoke @auth
Feature: SPC to add dynamic promotions (Buy 3 Get 1 free) for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/user" page

  @desktop @dynamic
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Buy 3 Get 1 free) on cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".promotions-full-view-mode" should exist
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-4" element
    And I wait 15 seconds
    Then I should see "4"
    And I wait 5 seconds
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
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
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    Then the element ".checkout-link.submit" should exist

  @language @dynamic
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Buy 3 Get 1 free) on cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".promotions-full-view-mode" should exist
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-4" element
    And I wait 15 seconds
    Then I should see "4"
    And I wait 5 seconds
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    Then the element ".checkout-link.submit" should exist

  @mobile @dynamic
  Scenario: As an Authenticated User, I should be able to add dynamic promotions like (Buy 3 Get 1 free) on cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".promotions-full-view-mode" should exist
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element "#block-content .spc-main .spc-content .spc-cart-items .spc-promotions .promotion-label" should exist
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-4" element
    And I wait 15 seconds
    Then I should see "4"
    And I wait 5 seconds
    And the element ".totals .discount-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 30 seconds
    And I wait for AJAX to finish
    And the element ".promotion-label" should exist
    And the element ".totals .discount-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
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
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    Then the element ".checkout-link.submit" should exist
