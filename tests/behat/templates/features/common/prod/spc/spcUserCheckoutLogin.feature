@javascript @smoke @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @pbkaepprod @pbksapprod @coskwprod @cosaeprod @cossaprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @pbkaeprod @pbksaprod @westelmsapprod @westelmkwpprod @westelmaepprod @vskwpprod @pbkkwprod @mujiaeprod @mujisaprod @mujikwprod @westelmkwprod @bpkwprod @tbsegprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @westelmaeprod @westelmsaprod @mcsaprod @mcsapprod @mcaeprod @mcaepprod @vskwprod @mckwprod @mckwpprod @tbskwprod @bbwaeprod @bbwaepprod @bbwaepprod @bbwsaprod @bbwsapprod @bbwkwprod @flaeprod @flkwprod @flsaprod @flaepprod @flkwpprod @flsapprod @hmaeprod @hmkwprod @hmsaprod @hmaepprod @hmkwpprod @hmsapprod @vsaeprod @vssaprod @vsaepprod @vssapprod @pbaeprod @pbkwprod @pbsaprod @pbaepprod @pbkwpprod @pbsapprod
Feature: Test the Checkout Login functionality

  Scenario: As a user, I should be able to see cart content added as anonymous user once I log into the site
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    Given I am on "user/login"
    And I wait 10 seconds
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I click on "#mini-cart-wrapper a.cart-link" element
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
    And I should see an ".totals" element
    And I should see an ".grand-total" element
    And I should see an ".value .price .price-currency" element
    And I should see an ".value .price .price-amount" element
    Then I should see "{order_summary}"
    Then I should see an ".delivery-vat" element
