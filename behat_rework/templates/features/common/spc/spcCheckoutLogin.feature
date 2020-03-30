@javascript
Feature: Test the Checkout Login functionality

  Scenario: Anonymous user should be able to see correct fields on user register page
    Given I am on "user/register"
    And I wait 10 seconds
    Then I should see "{create_account}"
    And the element ".c-content__region .region__content #block-content #user-register-form" should exist
    And the element "#user-register-form #edit-field-first-name-wrapper" should exist
    And the element "#user-register-form #edit-field-last-name-0-value" should exist
    And the element "#user-register-form #edit-mail" should exist
    And the element "#user-register-form #edit-pass" should exist
    And the element "#user-register-form .captcha" should exist
    And the element "#user-register-form #edit-field-subscribe-newsletter-value" should exist
    And the element "#user-register-form #edit-actions #edit-submit" should exist
    And the element ".c-content__region .region__content #block-alshayasocialloginblock" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_facebook" should exist
    And the element "#block-alshayasocialloginblock .alshaya-social .social_auth_google" should exist
    And the element ".c-content__region .region__content #block-alshayasignupsigninbuttonsblock" should exist

  Scenario: As a Guest, I should be able to add more quantity
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
#
#  Scenario: Authenticated user should be able to login into the system
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "prachi.nagpal@acquia.com"
    And I fill in "edit-pass" with "Alshaya@12"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    Then I should see "My Account"
    And I should see "recent orders"
    And the element "#block-userrecentorders .no--orders" should exist
    And the element "#block-userrecentorders .subtitle" should exist
    And the element "#block-userrecentorders .edit-account" should exist
    And I should see "You have no recent orders to display."
#
#  Scenario: As an authenticated user, I should be able to see all the sections after logging in
    Then I should see the link "my account" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "orders" in "#block-alshayamyaccountlinks .my-account-nav" section
    Then I should see the link "contact details" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "address book" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "communication preferences" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "change password" in "#block-alshayamyaccountlinks .my-account-nav" section
    And the element "#block-myaccountneedhelp" should exist
    And the element "#block-content .account-content-wrapper .email" should exist
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
