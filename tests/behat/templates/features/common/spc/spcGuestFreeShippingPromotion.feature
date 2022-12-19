@javascript @free-shipping @promotion @smoke @mckwuat @mcsauat @mcaeuat
Feature: SPC to add Free shipping promotion on cart for Guest user

  @desktop @guest @free-shipping @promotion
  Scenario: As a Guest User , I should be able to add Free shipping promotion of product on cart
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
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
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"

  @language @guest @free-shipping @promotion
  Scenario: As a Guest User, I should be able to add Free shipping promotion of product on cart for second language
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
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
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 20 seconds
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"

  @mobile @guest @free-shipping @promotion
  Scenario: As a Guest User, I should be able to add Free shipping promotion of product on cart
    Given I am on "{spc_promotion_listing_page}"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
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
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 20 seconds
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"
