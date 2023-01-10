@javascript @auth @expressDelivery
Feature: SPC Checkout Express Delivery feature testing for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait for the page to load

  @expressDelivery
  Scenario: As an Authenticated user, I should be able to verify the Express Delivery options in All Filters and on PDP page
    Given I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "Express" delivery filter
    Then I should see an "#plp-hits .express_delivery" element
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see an ".express-delivery .express-delivery-text" element
    Then I should see an ".express-delivery .express-delivery-text:nth-child(2)" element
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#ui-id-1" element
    Then the element "#express-delivery-options" should exist
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait 5 seconds
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    Then I should see an ".spc-delivery-area .delivery-type-wrapper .express-delivery" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait 2 seconds
    And I click on "div.select-area-link" element
    And I wait 5 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#delivery-area-select" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
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
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @language @desktop
  Scenario: As an authenticated user, I should be able to verify the Express Delivery options in All Filters and on PDP page in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And  I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "Express" delivery filter
    Then I should see an "#plp-hits .express_delivery" element
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see an ".express-delivery .express-delivery-text" element
    Then I should see an ".express-delivery .express-delivery-text:nth-child(2)" element
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#ui-id-1" element
    Then the element "#express-delivery-options" should exist
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait 5 seconds
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    Then I should see an ".spc-delivery-area .delivery-type-wrapper .express-delivery" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait 2 seconds
    And I click on "div.select-area-link" element
    And I wait 5 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#delivery-area-select" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
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
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @mobile
  Scenario: As an authenticated user, I should be able to verify the Express Delivery options in All Filters and on PDP page on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    And  I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "Express" delivery filter
    Then I should see an "#plp-hits .express_delivery" element
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see an ".express-delivery .express-delivery-text" element
    Then I should see an ".express-delivery .express-delivery-text:nth-child(2)" element
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    And I click on "#ui-id-1" element
    Then the element "#express-delivery-options" should exist
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait 5 seconds
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    Then I should see an ".spc-delivery-area .delivery-type-wrapper .express-delivery" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait 2 seconds
    And I click on "div.select-area-link" element
    And I wait 5 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#delivery-area-select" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
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
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
