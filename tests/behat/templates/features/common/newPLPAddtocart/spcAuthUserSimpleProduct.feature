@javascript @plp-addtocart @smoke @auth @vsaeprod @tbsegpprod @vssapprod @vskwpprod @mckwpprod @bpaepprod @bpaeqa @mckwqa @bpaeprod @bpkwprod @bpsaprod @tbskwprod @tbsegprod @vssaprod @vskwprod @bbwsaprod @bbwkwprod @bbwaeprod
Feature: Testing new PLP-Add to cart functionality for Authenticated user on simple product

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  @desktop @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add products to cart on product listing page
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for element ".c-products__item"
    And I click jQuery ".c-products__item:first-child div.addtobag-simple-button-container button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".qty-text-wrapper"
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 2 seconds
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page

  @language @plp-addtocart
  Scenario: As an Authenticated user, I should be able to add products to cart on product listing page for second language
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for element ".c-products__item"
    When I follow "{language_link}"
    And I wait for element "#block-page-title"
    And I click jQuery ".c-products__item:first-child div.addtobag-simple-button-container button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".qty-text-wrapper"
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 2 seconds
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page

  @mobile @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add products to cart on product listing page for mobile
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for element ".c-products__item"
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_class} a" on page
    And I wait for element "#block-page-title"
    And I click jQuery ".c-products__item:first-child div.addtobag-simple-button-container button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait for element ".qty-text-wrapper"
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 2 seconds
    And I wait for element "#spc-cart"
    Then I should be on "/cart" page
