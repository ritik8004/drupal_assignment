@javascript @auth @Tabby @homeDelivery @bbwaeprod @bbwsaprod @cossaprod
Feature: SPC Checkout Home Delivery of Tabby payment for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait for the page to load

  @hd @tabby
  Scenario: As an Authenticated user, I should be able to checkout using Tabby payment method
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element "div.tabby #tabby-promo-pdp-main" should exist
    And the element "div.tabby #tabby-promo-pdp-main span.tabby-promo-snippet__text" should exist
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And the element "div.tabby-promo-snippet" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-tabby" element on page
    And I wait 30 seconds
    And I wait for AJAX to finish
    Then the element "#tabby-card-checkout" should exist
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should see tabby payment window

  @language @tabby
  Scenario: As an Authenticated user, I should be able to checkout using Tabby payment method for second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And the element "div.tabby #tabby-promo-pdp-main" should exist
    And the element "div.tabby #tabby-promo-pdp-main span.tabby-promo-snippet__text" should exist
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And the element "div.tabby-promo-snippet" should exist
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-tabby" element on page
    And I wait 30 seconds
    And I wait for AJAX to finish
    Then the element "#tabby-card-checkout" should exist
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should see tabby payment window
