@javascript @auth @checkoutPayment @same-dayDelivery @mujiqauat @bbwsauat @hmaeuat @hmsauat
Feature: SPC Checkout Same Day Delivery feature testing for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_basket_page}"
    And I wait for the page to load

  @same-dayDelivery
  Scenario: As an Authenticated user, I should be able to verify the Same Day options in All Filters and on PDP page
    Given I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    And I wait 2 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I should see an "#delivery-area-select" element
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
    Then I should see "{order_detail}"

  @language @desktop
  Scenario: As an authenticated user, I should be able to verify the Same-Day Delivery options in All Filters and on PDP page in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    And I wait 2 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I should see an "#delivery-area-select" element
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
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

  @mobile
  Scenario: As an authenticated user, I should be able to verify the Same-day Delivery options in All Filters and on PDP page on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I should see an ".plp-facet-product-filter .show-all-filters-algolia" element
    And I apply the "SameDay" delivery filter
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    Then I should see an ".express-delivery .express-delivery-text" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I click on "#ui-id-1" element
    And I should see an "#pdp-area-select" element
    And I click on "#pdp-area-select" element
    And I wait for AJAX to finish
    Then I should see an ".spc-delivery-area" element
    Then I should see an ".spc-delivery-area .governate-label" element
    And I click on ".area-list-block-content ul li:first-child" element
    And I wait for AJAX to finish
    And I click on "div.select-area-link" element
    And I wait 2 seconds
    Then I should see an "#pdp-area-select .delivery-area-name" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I should see an "#delivery-area-select" element
    When I follow "continue to checkout"
    And I wait for AJAX to finish
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
    Then I should see "{order_detail}"
