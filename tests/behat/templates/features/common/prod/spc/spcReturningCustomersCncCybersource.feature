@javascript @returnUser @cybersourcePayment @clickCollect
Feature: SPC Checkout using Click & Collect store for returning customer using Cybersource Payment Metod

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds

  @cc @cnc @cybersource
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
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
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-cybersource" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @cnc @language @desktop @cybersource
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
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
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-cybersource" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @cnc @language @mobile @cybersource
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
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
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods #payment-method-cybersource" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
