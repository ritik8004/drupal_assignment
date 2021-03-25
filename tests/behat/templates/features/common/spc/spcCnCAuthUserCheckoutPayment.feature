@javascript @checkoutPayment @auth @clickCollect @hmaeuat @mckwuat @hmkwuat @hmsauat @flkwuat @vssauat @vsaeuat @flaeuat @bbwaeuat
Feature: SPC Checkout using Click & Collect store for Authenticated user using Checkout (2D) Cards

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @cnc @checkout_com
  Scenario: As an authenticated user, I should be able to checkout using click and collect with credit card
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    Then I should be on "/checkout/confirmation" page

  @cc @cnc @mobile @checkout_com
  Scenario: As an authenticated user, I should be able to checkout using click and collect with credit card (checkout_com)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page

  @cc @cnc @language @desktop @checkout_com
  Scenario: As an authenticated user, I should be able to checkout using click and collect with credit card (checkout_com)
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page

  @cc @cnc @language @mobile @checkout_com
  Scenario: As an authenticated user, I should be able to checkout using click and collect with credit card (checkout_com)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
