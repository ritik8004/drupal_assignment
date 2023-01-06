@javascript @smoke @pbkkwuat @pbksauat @coskwuat @cosaeuat @mujikwuat @mujisauat @mujiaeuat @pbkaeuat @bpaeuat @bpkwuat @bpsauat @tbseguat @hmaeuat @mckwuat @vsaeuat @aeoaeuat @aeokwuat @aeosauat @tbskwuat @westelmaeuat @westelmsauat @westelmkwuat @vssauat @flkwuat @bbwkwuat @hmkwuat @hmsauat @mcsauat @mcaeuat @vskwuat @vsaeuat @flkwuat @flsauat @flaeuat @bbwsauat @bbwaeuat
Feature: Test basket page

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @desktop
  Scenario: As a Guest, I should be able to add more quantity
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    Then I should see "2"
    And I wait for AJAX to finish
    Then the price for product should be doubled

  @desktop
  Scenario: As a Guest, I should be able to see the header
    When I scroll to top
    Then I should see "{create_account}"
    Then I should see "{sign_in}"
    Then I should see "{find_store}"
    Then I should see "{language_link}"
    Then I should see an ".acq-mini-cart" element
    Then I should see an "#alshaya-algolia-autocomplete" element
    Then I should see an ".plp-facet-product-filter" element
  @mobile
  Scenario: As a Guest, I should be able to see the header (mobile)
    When I scroll to top
    Then I should see a "#block-mobilenavigation a.store" element on page
    Then I should see a "#block-mobilenavigation a.mobile--search" element on page
    Then I should see a "#block-alshayareactcartminicartblock #mini-cart-wrapper a.cart-link" element on page
    Then I should see a "#block-mobilenavigation a.hamburger--menu" element on page
    Then I click on "#block-mobilenavigation a.hamburger--menu" element
    And I wait for AJAX to finish
    Then I click on "#block-alshayamainmenu .account.one" element
    And I wait for AJAX to finish
    Then I should see a "#block-account-menu .sign-in-mobile" element on page
    Then I should see a "#block-account-menu .register-link" element on page
    Then I click on "#block-alshayamainmenu .mobile--close" element

  @desktop
  Scenario: As a Guest, I should be able to see the products added to basket and the header
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
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
    And I should see an ".totals" element
    And I should see an ".grand-total" element
    And I should see an ".value .price .price-currency" element
    And I should see an ".value .price .price-amount" element
    Then I should see "{order_summary}"
    Then I should see an ".delivery-vat" element

  @mobile
  Scenario: As a Guest, I should be able to see the products added to basket and the header (mobile)
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I scroll to the "#block-content .vat-text-footer" element
    Then I should see "{promo_code}"
    And I should see an ".totals" element
    And I should see an ".grand-total" element
    And I should see an ".value .price .price-currency" element
    And I should see an ".value .price .price-amount" element
    Then I should see an ".delivery-vat" element

  Scenario: As a Guest, I should be able to remove products from the basket
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for the page to load
    And I should not see an ".totals" element
    And I should not see an ".grand-total" element
    And I should not see an ".value .price .price-currency" element
    And I should not see an ".value .price .price-amount" element
    Then I should not see an ".delivery-vat" element

  @language @desktop
  Scenario: As a Guest, I should be able to add more quantity in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    And I should see "2"
    Then the price for product should be doubled

  @language @mobile
  Scenario: As a Guest, I should be able to add more quantity in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 5 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait for AJAX to finish
    #-Product quantity loader
    And I wait 2 seconds
    And I should see "2"
    Then the price for product should be doubled

  @desktop @language
  Scenario: As a Guest, I should be able to see the header in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I scroll to top
    Then I should see "{language_create_account}"
    Then I should see "{language_sign_in}"
    Then I should see "{language_find_store}"
    Then I should see "{second_language_link}"
    Then I should see an ".acq-mini-cart" element
    Then I should see an "#alshaya-algolia-autocomplete" element
    Then I should see an ".plp-facet-product-filter" element

  @mobile @language
  Scenario: As a Guest, I should be able to see the header in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I scroll to top
    Then I should see a "#block-mobilenavigation a.store" element on page
    Then I should see a "#block-mobilenavigation a.mobile--search" element on page
    Then I should see a "#block-alshayareactcartminicartblock #mini-cart-wrapper a.cart-link" element on page
    Then I should see a "#block-mobilenavigation a.hamburger--menu" element on page
    Then I click on "#block-mobilenavigation a.hamburger--menu" element
    And I wait for AJAX to finish
    Then I click on "#block-alshayamainmenu .account.one" element
    And I wait for AJAX to finish
    Then I should see a "#block-account-menu .sign-in-mobile" element on page
    Then I should see a "#block-account-menu .register-link" element on page
    Then I click on "#block-alshayamainmenu .mobile--close" element

  @language @desktop
  Scenario: As a Guest, I should be able to see the products added to basket and the header in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I should see an ".totals" element
    And I should see an ".grand-total" element
    And I should see an ".value .price .price-currency" element
    And I should see an ".value .price .price-amount" element
    Then I should see an ".delivery-vat" element

  @mobile @language
  Scenario: As a Guest, I should be able to see the products added to basket and the header (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I scroll to the "#block-content .vat-text-footer" element
    And I wait 5 seconds
    And I should see an ".totals" element
    And I should see an ".grand-total" element
    And I should see an ".value .price .price-currency" element
    And I should see an ".value .price .price-amount" element
    Then I should see an ".delivery-vat" element

  @language @desktop
  Scenario: As a Guest, I should be able to remove products from the basket in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for the page to load
    And I should not see an ".totals" element
    And I should not see an ".grand-total" element
    And I should not see an ".value .price .price-currency" element
    And I should not see an ".value .price .price-amount" element
    Then I should not see an ".delivery-vat" element

  @language @mobile
  Scenario: As a Guest, I should be able to remove products from the basket in second language (mobile)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I verify the wishlist popup block if enabled and remove the cart item
    And I wait for the page to load
    And I should not see an ".totals" element
    And I should not see an ".grand-total" element
    And I should not see an ".value .price .price-currency" element
    And I should not see an ".value .price .price-amount" element
    Then I should not see an ".delivery-vat" element
