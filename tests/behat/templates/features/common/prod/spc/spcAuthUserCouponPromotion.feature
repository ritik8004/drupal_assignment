@javascript @coupon-promotion @discount @smoke @auth
Feature: SPC to add coupon promotions & get discount in cart page for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/user" page

  @desktop @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @language @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist


  @mobile @dynamic
  Scenario: As an Authenticated User, I should be able to add coupon promotions & get discount direct on Cart page
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    And the element ".promotions" should exist
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".content__title_wrapper .promotions" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    And the element ".promotion-label" should exist
    And the element ".dynamic-promotion-wrapper" should exist
    And the element ".promotion-text" should exist
    And the element ".block-content .promotion-available-code" should exist
    And I click jQuery ".block-content .promotion-coupon-code" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the promo code should be applied
    And the element ".totals .discount-total" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
