@javascript @auth @same-dayDelivery @mujiqaprod @flqaprod
Feature: SPC Checkout Same Day Delivery feature testing for Authenticated user

  Background:
    Given I go to in stock category page
    And I wait for element "#block-page-title"

  @same-dayDelivery
  Scenario: As a Guest user, I should be able to verify the Same Day options in All Filters and on PDP page
    Given I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @language @desktop
  Scenario: As a Guest user, I should be able to verify the Same-Day Delivery options in All Filters and on PDP page in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @mobile
  Scenario: As a Guest user, I should be able to verify the Same-day Delivery options in All Filters and on PDP page on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-home_delivery"
