@javascript
Feature: Test basket page

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for the page to load
    And I wait for AJAX to finish

  Scenario: As a Guest, I should be able to add more quantity
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I go to "/cart"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled

  @desktop
  Scenario: As a Guest, I should be able to see the header and the footer
    When I scroll to top
    Then I should see the link "{create_account}"
#    Then I should see the link "{sign_in}"
    Then I should see the link "{find_store}"
    Then I should see the link "{language_link}"
    Then I should see "{sort_filter}"
    Then I should see "{price_filter}"
    Then I should see "{color_filter}"
    Then I should see "{size_filter}"
    Then I should see "{filters}"
    Then I should see "{brand_filter}"
    Then I should see "{collection_filter}"
    Then I should see "{promotional_filter}"

  @mobile
  Scenario: As a Guest, I should be able to see the header and the footer
    When I scroll to top
    Then I should see a "#block-mobilenavigation a.store" element on page
    Then I should see a "#block-mobilenavigation a.hamburger--menu" element on page
    Then I click on "#block-mobilenavigation a.hamburger--menu" element
    And I wait 10 seconds
    Then I click on "#block-alshayamainmenu .account.one" element
    And I wait 10 seconds
    Then I should see a "#block-account-menu .sign-in-mobile" element on page
    Then I should see a "#block-account-menu .createanaccount-link" element on page
    Then I should see a "#block-account-menu .signin-link" element on page
    Then I click on "#block-alshayamainmenu .mobile--close" element
    And I wait 5 seconds
    Then I should see a "#block-categoryfacetplp" element on page
    Then I should see a "#block-alshaya-plp-facets-block-all" element on page

  Scenario: As a Guest, I should be able to see the products added to basket and the header and footer
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I go to "/cart"
    And I wait for the page to load
    And I wait 10 seconds
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    Then I should see "{order_summary}"
    Then I should see "{promo_code}"
    And I should see "{excluding_delivery}"
    And I should see "{vat}"

  Scenario: As a Guest, I should be able to remove products from the basket
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I go to "/cart"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait 30 seconds
    Then I should not see "Subtotal"
    Then I should not see "Order Total"
    Then I should not see "order summary"
    Then I should not see "have a promo code?"
    Then I should not see "excluding delivery"
    Then I should see "Your Shopping Bag Is Empty."
