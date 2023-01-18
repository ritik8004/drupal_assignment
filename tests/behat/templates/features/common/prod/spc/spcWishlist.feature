@javascript @guest @Wishlist @homeDelivery @bpaeprod @bpkwprod @bpsaprod @hmkwprod @hmaeprod @coskwprod @cosaeprod @cossaprod @vskwprod @vssaprod @vsaeprod @flkwprod @flsaprod @flaeprod @mujiaeprod @mujikwprod @tbskwprod @bbwaeprod @bbwkwprod @bbwsaprod @mcaeprod @mckwprod @mcsaprod
@bpaepprod @bpkwpprod @bpsapprod @hmkwpprod @hmaepprod @coskwpprod @cosaepprod @cossapprod @vskwpprod @vssapprod @vsaepprod @flkwpprod @flsapprod @flaepprod @mujiaepprod @mujikwpprod @tbskwpprod @bbwaepprod @bbwkwpprod @bbwsapprod @mcaepprod @mckwpprod @mcsapprod
Feature: SPC Checkout Wishlist feature for Guest user

  Scenario: As a Guest user, I should be able to see and add Wishlist products from PLP page
    Given I go to in stock category page
    And I wait for element "#block-page-title"
    And I wait for element ".c-products__item"
    And I should see the Wishlist icon
    When I click on the Wishlist icon
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I wait for element "#my-wishlist"
    And I should see an ".login-message" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".empty-message"
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I click on ".spc-product-wishlist-link .wishlist-text:first-child" element
    And I wait for AJAX to finish
    And I wait for element ".spc-empty-container"
    Then I should see an "#spc-cart .spc-empty-text" element

  Scenario: As a Guest User, I should be able to see and add Wishlist product from PDP page and place an order
    When I go to in stock product page
    And I wait for element ".content__sidebar"
    When I click on "div.wishlist-icon" element
    And I wait for element ".wishlist-header a"
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I wait for element "#my-wishlist"
    And I should not see an ".empty-message" element
    And I click on Add-to-cart button
    And I wait 2 seconds
    And I wait for AJAX to finish
    And I wait for element ".empty-message"
    And I wait for element ".cart-link .quantity"
    And I am on "/wishlist"
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I click on "#spc-cart .spc-cart-items .spc-product-tile-actions .spc-remove-btn" element
    And I wait for AJAX to finish
    And I wait for element ".wishlist-popup-block"
    And I should see an "button#wishlist-yes" element
    And I should see an "button#wishlist-no" element
    And I click on ".wishlist-popup-block a.close-modal" element
    And I wait for AJAX to finish
    And I should not see an ".wishlist-popup-block" element
    Then I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "checkout as guest"
    And I wait for element "#spc-checkout .home-delivery"
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  Scenario: As a Guest User, I want to share the Wishlist through copying url or email
    Given I go to in stock category page
    And I wait for element ".c-products__item"
    When I click on the Wishlist icon
    Then I should see the Wishlist icon active
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I should see an "#wishlist-share" element
    And I click on "#wishlist-share" element
    And I wait for element "#block-content"
    Then I should be on "/user/login" page
    And I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    And I click on ".wishlist-header a" element
    Then I should be on "/wishlist" page
    And I click on "#wishlist-share" element
    And I wait for element ".wishlist-share-popup-block"
    And I click on ".wishlist-share-popup-block .actions .copy-share-link" element
