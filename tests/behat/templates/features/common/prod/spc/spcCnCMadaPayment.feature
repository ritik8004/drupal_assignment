@javascript @guest @cnc @madaPayment @clickCollect @vssapprod @bbwsapprod @hmsapprod @flsaprod @vssaprod @bbwsaprod @hmsaprod @flsaprod
Feature: SPC Checkout Click & Collect using Mada Card Payment Method for Guest Users

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds

  @cc @hd @checkout_com @visa @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) with MADA Cards (VISA Card)
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @checkout_com @visa @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @checkout_com @visa @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @checkout_com @mastercard @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) with MADA Cards (Mastercard Card)
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @checkout_com @mastercard @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @checkout_com @mastercard @mada
  Scenario: As a Guest, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
