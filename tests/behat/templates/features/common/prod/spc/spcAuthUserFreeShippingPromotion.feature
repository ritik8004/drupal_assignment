@javascript @free-shipping @promotion @smoke @auth
Feature: SPC to add Free shipping promotion on cart for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/user" page

  @desktop @free-shipping @promotion
  Scenario: As an Authenticated User, I should be able to add Free shipping promotion of product on cart
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 30 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 20 seconds
    And the element "#block-content #spc-cart .spc-pre-content .dynamic-promotion-wrapper div.inactive-promotions" should exist
    And I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait 15 seconds
    Then I should see "3"
    And I wait 5 seconds
    And the element ".total-line-item .delivery-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".total-line-item .delivery-total" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @language @desktop @free-shipping @promotion
  Scenario: As an Authenticated User, I should be able to add Free shipping promotion of product on cart in second language
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    And I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 20 seconds
    And I wait for AJAX to finish
    And the element "#block-content #spc-cart .spc-pre-content .dynamic-promotion-wrapper div.inactive-promotions" should exist
    And I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait 15 seconds
    Then I should see "3"
    And I wait 5 seconds
    And the element ".total-line-item .delivery-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".total-line-item .delivery-total" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @mobile @free-shipping @promotion
  Scenario: As an Authenticated User, I should be able to add Free shipping promotion of product on cart
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 30 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 20 seconds
    And the element "#block-content #spc-cart .spc-pre-content .dynamic-promotion-wrapper div.inactive-promotions" should exist
    And I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I wait 10 seconds
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait 15 seconds
    Then I should see "3"
    And I wait 5 seconds
    And the element ".total-line-item .delivery-total" should exist
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".total-line-item .delivery-total" should exist
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist
