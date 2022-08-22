@javascript @plp-addtocart @smoke @guest @mckwqa @tbskwprod @tbsegprod @vssaprod @vskwprod @tbsegprod @tbsegpprod @vssapprod @vskwpprod @mckwpprod @bpaepprod
Feature: Testing new PLP-Add to cart functionality for Guest user

  @desktop @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart on product listing page
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row:first-child button.addtobag-config-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @desktop @plp-addtocart @language
  Scenario: As an Authenticated User, I should be able to add configurable products to cart on product listing page for second language
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row:first-child button.addtobag-config-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page
    
  @mobile @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart on product listing page for mobile
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page
