@javascript @madaPayment @auth @clickCollect @vssapprod @bbwsapprod @hmsapprod @flsaprod @vssaprod @bbwsaprod @hmsaprod @flsaprod
Feature: SPC Checkout Click & Collect using Mada Payment Method for Authenticated User

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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_visa_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {language_store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_visa_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {language_store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_visa_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_master_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {language_store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_master_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-click_and_collect" element on page
    And I wait 10 seconds
    Then the "delivery-method-click_and_collect" checkbox should be checked
    And I wait for the page to load
    Then I add the store details with:
      | edit-store-location | {language_store_area} |
      | mobile              | {mobile}     |
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-checkout_com" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the "payment-method-checkout_com" checkbox should be checked
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cc-number input" with "{spc_mada_master_card}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    And I fill in an element having class ".payment-method-checkout_com .spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-section-billing-address" element
    When I add CnC billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I wait 50 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
