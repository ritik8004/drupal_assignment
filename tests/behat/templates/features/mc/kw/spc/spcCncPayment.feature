@javascript
Feature: SPC Checkout Click & Collect

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cc @cnc @checkout_com
  Scenario: As a Guest, I should be able to checkout using click and collect with credit card (checkout_com)
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    Then I should be on "/cart/login" page
    And I wait 10 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 10 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 10 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "4242424242424242"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "03/22"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "100"
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{cnc_delivery_type}"
    Then I should see "{payment_type_text}"
    Then I should see "{cc_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @cnc @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 5 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 5 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-knet" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait for AJAX to finish
    And I wait 10 seconds
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "0000000001"
    And I select "9" from "debitMonthSelect"
    And I select "2021" from "debitYearSelect"
    And I fill in "cardPin" with "1234"
    And I press "proceed"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{cnc_delivery_type}"
    Then I should see "{payment_type_text}"
    Then I should see "{knet_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @cnc @language @desktop @checkout_com
  Scenario: As a Guest, I should be able to checkout using click and collect with credit card (checkout_com)
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
    Then I should be on "/{language_short}/cart/login" page
    And I wait 10 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 5 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 10 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "4242424242424242"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "03/22"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "100"
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_cnc_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cc_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @cnc @language @desktop @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 5 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 5 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-knet" element on page
    And I wait for AJAX to finish
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 5 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "0000000001"
    And I select "9" from "debitMonthSelect"
    And I select "2021" from "debitYearSelect"
    And I fill in "cardPin" with "1234"
    And I press "proceed"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_cnc_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_knet_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @cnc @language @mobile @checkout_com
  Scenario: As a Guest, I should be able to checkout using click and collect with credit card (checkout_com)
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
    Then I should be on "/{language_short}/cart/login" page
    And I wait 10 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 5 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 10 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "4242424242424242"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "03/22"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "100"
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_cnc_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cc_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @cnc @language @mobile @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 5 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 5 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  #click-and-collect-list-view li[data-index=0] .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-knet" element on page
    And I wait for AJAX to finish
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 5 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "0000000001"
    And I select "9" from "debitMonthSelect"
    And I select "2021" from "debitYearSelect"
    And I fill in "cardPin" with "1234"
    And I press "proceed"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_cnc_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_knet_payment_type}"
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .vat-text-footer" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
#  And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"
