@javascript @plp-addtocart @smoke @auth @bpaeqa @vsaeuat @mckwqa @vsaeprod @tbskwprod @tbsegprod @vssaprod @vskwprod @tbsegprod @tbsegpprod @vssapprod @vskwpprod @mckwpprod @bpaepprod
Feature: Testing new PLP-Add to cart functionality for Authenticated user on config product

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for element "#block-page-title"
    And I click jQuery ".c-products__item.views-row:first-child button.addtobag-config-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".product-drawer-container"
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I wait for element "#block-alshayareactcartminicartblock .cart-link-total"
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page

  @desktop @plp-addtocart @language
  Scenario: As an Authenticated User, I should be able to add configurable products to cart for second language
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for element "#block-page-title"
    When I follow "{language_link}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row:first-child button.addtobag-config-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".product-drawer-container"
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I wait for element "#block-alshayareactcartminicartblock .cart-link-total"
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page

  @mobile @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart for mobile
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for element "#block-page-title"
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_class} a" on page
    And I wait for element "#block-page-title"
    And I click jQuery ".c-products__item.views-row:first-child button.addtobag-config-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".product-drawer-container"
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I wait for element "#block-alshayareactcartminicartblock .cart-link-total"
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page
