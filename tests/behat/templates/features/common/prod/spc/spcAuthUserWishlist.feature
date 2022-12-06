@javascript @auth @Wishlist @homeDelivery @bpaeprod @bpkwprod @bpsaprod @hmkwprod @hmaeprod @coskwprod @cosaeprod @cossaprod @vskwprod @vssaprod @vsaeprod @flkwprod @flsaprod @flaeprod @mujiaeprod @mujikwprod @tbskwprod @bbwaeprod @bbwkwprod @bbwsaprod @mcaeprod @mckwprod @mcsaprod
@bpaepprod @bpkwpprod @bpsapprod @hmkwpprod @hmaepprod @coskwpprod @cosaepprod @cossapprod @vskwpprod @vssapprod @vsaepprod @flkwpprod @flsapprod @flaepprod @mujiaepprod @mujikwpprod @tbskwpprod @bbwaepprod @bbwkwpprod @bbwsapprod @mcaepprod @mckwpprod @mcsapprod

Feature: SPC Checkout Wishlist feature for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load
    And I should see the Wishlist icon

  Scenario: As an Authenticated user, I should be able to see and add Wishlist products from PLP page
    When I click on the Wishlist icon
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I wait 5 seconds
    And I click on Add-to-cart button
    And I wait 5 seconds
    And I should see a ".empty-message" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    And I click on ".spc-product-wishlist-link .wishlist-text:first-child" element
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see an "#spc-cart .spc-empty-text" element
    And I am on "/wishlist"
    And I wait 5 seconds
    And I click the element ".in-wishlist .wishlist-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I should see a ".empty-message" element

  Scenario: As an Authenticated User, I should be able to see and add Wishlist product from PDP page and place an order
    Given I am on "{spc_pdp_page}"
    When I click on the Wishlist icon
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I should not see an ".empty-message" element
    And I click on Add-to-cart button
    And I wait 5 seconds
    Then I should see an "#configurable-drawer .product-drawer-container" element
    And I scroll to the ".config-form-addtobag-button-wrapper" element
    And I click on "[id^='config-form-addtobag-button-']" element
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I am on "/wishlist"
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I should see an ".wishlist-popup-block" element
    And I should see an "button#wishlist-yes" element
    And I should see an "button#wishlist-no" element
    And I click on ".wishlist-popup-block a.close-modal" element
    And I wait 5 seconds
    And I should not see an ".wishlist-popup-block" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  Scenario: As an Authenticated User, I want to share the Wishlist through copying url or email
    When I click on the Wishlist icon
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I should see an "#wishlist-share" element
    And I click on "#wishlist-share" element
    And I wait 5 seconds
    And I wait for the page to load
    Then I should see an ".wishlist-share-popup-block" element
    And I click on ".wishlist-share-popup-block .actions .copy-share-link" element
    And I click on ".wishlist-share-popup-block .actions .email-share-link" element
    Then I click on ".wishlist-share-popup-block a.close-modal" element
    Then I should not see an ".wishlist-share-popup-block" element
