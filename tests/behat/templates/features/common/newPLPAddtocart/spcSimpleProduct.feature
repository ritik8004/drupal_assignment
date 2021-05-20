@javascript @plp-addtocart @smoke @guest @bpaeuat
Feature: Testing new PLP-Add to cart functionality for Guest user on simple product

  @desktop @plp-addtocart
  Scenario: As a Guest User, I should be able to add products to cart on product listing page
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait 5 seconds
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @language @plp-addtocart
  Scenario: As a Guest User, I should be able to add products to cart on product listing page for second language
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @mobile @plp-addtocart
  Scenario: As a Guest User, I should be able to add products to cart on product listing page for mobile
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page
