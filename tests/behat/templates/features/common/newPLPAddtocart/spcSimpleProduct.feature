@javascript @plp-addtocart @smoke @guest @vsaeprod @tbsegprod @tbsegpprod @vssapprod @vskwpprod @mckwpprod @bpaepprod @tbskwprod @tbsegprod @vssaprod @vskwprod @bbwsaprod @bbwkwprod @bbwaeprod @bpaeprod @bpkwprod @bpsaprod
Feature: Testing new PLP-Add to cart functionality for Guest user on simple product

  @desktop @plp-addtocart
  Scenario: As a Guest User, I should be able to add products to cart on product listing page
    Given I am on "{spc_plp_add_to_cart_single}"
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

  @language @plp-addtocart
  Scenario: As a Guest User, I should be able to add products to cart on product listing page for second language
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for element "#block-page-title"
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
  Scenario: As a Guest User, I should be able to add products to cart on product listing page for mobile
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for element "#block-page-title"
    And I wait for the page to load
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
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
