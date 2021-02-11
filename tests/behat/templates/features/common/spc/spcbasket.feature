@javascript @smoke @pbsauat @hmaeuat @mckwuat @vssauat @pbaeuat @pbkwuat @pbsauat @bbwkwuat @hmkwuat @hmsauat @mcsauat @mcaeuat @vskwuat @vsaeuat @flkwuat @flsauat @flaeuat @bbwsauat @bbwaeuat
Feature: Test basket page

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: As a Guest, I should be able to add more quantity
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
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
    Then I should see "{sign_in}"
    Then I should see the link "{find_store}"
    Then I should see the link "{language_link}"
    Then I should see "{sort_filter}"
    Then I should see "{price_filter}"
    Then I should see "{color_filter}"
    Then I should see "{brand_filter}"
    Then I should see "{filters}"

  @mobile
  Scenario: As a Guest, I should be able to see the header and the footer (mobile)
    When I scroll to top
    Then I should see a "#block-mobilenavigation a.store" element on page
    Then I should see a "#block-mobilenavigation a.mobile--search" element on page
    Then I should see a "#block-alshayareactcartminicartblock #mini-cart-wrapper a.cart-link" element on page
    Then I should see a "#block-mobilenavigation a.hamburger--menu" element on page
    Then I click on "#block-mobilenavigation a.hamburger--menu" element
    And I wait 10 seconds
    Then I click on "#block-alshayamainmenu .account.one" element
    And I wait 10 seconds
    Then I should see a "#block-account-menu .sign-in-mobile" element on page
    Then I should see a "#block-account-menu .register-link" element on page
    Then I click on "#block-alshayamainmenu .mobile--close" element
    And I wait 5 seconds
    Then I should see a "#block-alshaya-plp-facets-block-all" element on page

  @desktop
  Scenario: As a Guest, I should be able to see the products added to basket and the header and footer
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
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

  @mobile
  Scenario: As a Guest, I should be able to see the products added to basket and the header and footer (mobile)
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for the page to load
    And I wait 10 seconds
    Then I scroll to the "#block-content .vat-text-footer" element
    Then I should see "{promo_code}"
    And I wait 5 seconds
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    Then I should see "{order_summary}"
    And I should see "{excluding_delivery}"
    And I should see "{vat}"

  Scenario: As a Guest, I should be able to remove products from the basket
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I should not see "{subtotal}" on page
    Then I should not see "{order_total}" on page
    Then I should not see "{order_summary}" on page
    Then I should not see "{promo_code}" on page
    And I should not see "{excluding_delivery}" on page
    And I should not see "{vat}" on page
    Then I should see "{empty_bag}"
    And I should see the link "{continue_shopping}"

  @language @desktop
  Scenario: As a Guest, I should be able to add more quantity in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled

  @language @mobile
  Scenario: As a Guest, I should be able to add more quantity in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    And I wait 5 seconds
    Then the price for product should be doubled

  @desktop @language
  Scenario: As a Guest, I should be able to see the header and the footer in second language
    And I double click on "#block-languageswitcher li a.language-link:not(.is-active)" element
    And I wait for the page to load
    Then I should see the link "{language_create_account}"
    Then I should see "{language_sign_in}"
    Then I should see the link "{language_find_store}"
    Then I should see the link "{language_link}"
    Then I should see "{language_sort_filter}"
    Then I should see "{language_price_filter}"
    Then I should see "{language_color_filter}"
    Then I should see "{language_brand_filter}"
    Then I should see "{language_filters}"

  @mobile @language
  Scenario: As a Guest, I should be able to see the header and the footer in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I scroll to top
    Then I should see a "#block-mobilenavigation a.store" element on page
    Then I should see a "#block-mobilenavigation a.mobile--search" element on page
    Then I should see a "#block-alshayareactcartminicartblock #mini-cart-wrapper a.cart-link" element on page
    Then I should see a "#block-mobilenavigation a.hamburger--menu" element on page
    Then I click on "#block-mobilenavigation a.hamburger--menu" element
    And I wait 10 seconds
    Then I click on "#block-alshayamainmenu .account.one" element
    And I wait 10 seconds
    Then I should see a "#block-account-menu .sign-in-mobile" element on page
    Then I should see a "#block-account-menu .register-link" element on page
    Then I click on "#block-alshayamainmenu .mobile--close" element
    And I wait 5 seconds
    Then I should see a "#block-alshaya-plp-facets-block-all" element on page

  @language @desktop
  Scenario: As a Guest, I should be able to see the products added to basket and the header and footer in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for the page to load
    And I wait 10 seconds
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    Then I should see "{language_order_summary}"
    Then I should see "{language_promo_code}"
    And I should see "{language_excluding_delivery}"
    And I should see "{language_vat}"

  @mobile @language
  Scenario: As a Guest, I should be able to see the products added to basket and the header and footer (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for the page to load
    And I wait 10 seconds
    Then I scroll to the "#block-content .vat-text-footer" element
    And I wait 5 seconds
    Then I should see "{language_promo_code}"
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    Then I should see "{language_order_summary}"
    And I should see "{language_excluding_delivery}"
    And I should see "{language_vat}"

  @language @desktop
  Scenario: As a Guest, I should be able to remove products from the basket in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I should not see "{language_subtotal}" on page
    Then I should not see "{language_order_total}" on page
    Then I should not see "{language_order_summary}" on page
    Then I should not see "{language_promo_code}" on page
    And I should not see "{language_excluding_delivery}" on page
    And I should not see "{language_vat}" on page
    Then I should see "{language_empty_bag}"
    And I should see the link "{language_continue_shopping}"

  @language @mobile
  Scenario: As a Guest, I should be able to remove products from the basket in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 15 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I should not see "{language_subtotal}" on page
    Then I should not see "{language_order_total}" on page
    Then I should not see "{language_order_summary}" on page
    Then I should not see "{language_promo_code}" on page
    And I should not see "{language_excluding_delivery}" on page
    And I should not see "{language_vat}" on page
    Then I should see "{language_empty_bag}"
    And I should see the link "{language_continue_shopping}"
