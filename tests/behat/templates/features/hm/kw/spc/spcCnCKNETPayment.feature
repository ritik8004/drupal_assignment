@javascript @KNET
Feature: SPC Checkout Click and Collect using KNET payment method

  Background:
    Given I go to in stock category page
    And I wait 5 seconds
    And I wait for the page to load

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
    When I select the first autocomplete option for "{language_store_area}" on the "edit-store-location" field
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select "{spc_Knet_month}" from "debitMonthSelect"
    And I select "{spc_Knet_year}" from "debitYearSelect"
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "إرسال"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/{language_short}/checkout/confirmation" page

  @cc @cnc @desktop @knet
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
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 5 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select "{spc_Knet_month}" from "debitMonthSelect"
    And I select "{spc_Knet_year}" from "debitYearSelect"
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait 2 seconds
    And I press "Confirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/checkout/confirmation" page

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
    When I select the first autocomplete option for "{language_store_area}" on the "edit-store-location" field
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select "{spc_Knet_month}" from "debitMonthSelect"
    And I select "{spc_Knet_year}" from "debitYearSelect"
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "إرسال"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/{language_short}/checkout/confirmation" page

  @cc @cnc @mobile @knet
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
    And I wait 5 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-knet" element on page
    And I wait for AJAX to finish
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 5 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | address_line2                 | {floor}       |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I wait for the page to load
    And I wait 10 seconds
    And I select "{spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select "{spc_Knet_month}" from "debitMonthSelect"
    And I select "{spc_Knet_year}" from "debitYearSelect"
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait 2 seconds
    And I press "Confirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I wait 5 seconds
    Then I should be on "/checkout/confirmation" page
