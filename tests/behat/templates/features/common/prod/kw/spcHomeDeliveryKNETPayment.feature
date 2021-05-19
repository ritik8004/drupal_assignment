@javascript @KNetPayment @homeDelivery @auth @hmkwprod @mckwprod @bbwkwpprod @flkwprod @pbkwprod
Feature: SPC Checkout Home Delivery CC

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted" element
    And I wait 10 seconds

  @cc @hd @Knet
  Scenario: As a Guest, I should be able to checkout using CC
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
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    And the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @Knet
  Scenario: As a Guest, I should be able to checkout using CC in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I scroll to the ".region__highlighted" element
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
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I select the Knet payment method
    And I wait 10 seconds
    And the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @Knet
  Scenario: As a Guest, I should be able to checkout using CC in second language
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
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I select the Knet payment method
    And I wait 10 seconds
    And the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
