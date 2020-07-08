@javascript @auth
Feature: SPC Checkout Home Delivery using checkout_com method (2D cards) for Authenticated user

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_user_email}"
    And I fill in "edit-pass" with "{spc_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element

  @cc @hd @checkout_com @test
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com)
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
      | mobile   | {mobile}        |
    And I wait 50 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @checkout_com
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted " element
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
      | mobile   | {mobile}        |
    And I wait 50 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @checkout_com
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
      | mobile   | {mobile}        |
    And I wait 50 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
