@javascript @smoke @pbsauat @hmaeuat @mckwuat @vssauat @bbwkwuat
Feature: Test the Checkout Login functionality

  Scenario: As a user, I should be able to see cart content added as anonymous user once I log into the site
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for the page to load
    And I wait 10 seconds
    Then the element "#block-content .spc-main .spc-content .spc-cart-item" should exist
    Then the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-tile" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-tile .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-container .spc-product-title-price .spc-product-title" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-container .spc-product-title-price .spc-product-price" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-container .spc-product-title-price .spc-product-price .price-block .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-container .spc-product-title-price .spc-product-price .price-block .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-tile-actions .spc-remove-btn" should exist
    And the element "#block-content .spc-main .spc-content .spc-cart-item .spc-product-tile-actions .qty" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-promo-code-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    Then I should see "{order_summary}"
    Then I should see "{promo_code}"
    And I should see "{excluding_delivery}"
    And I should see "{vat}"
