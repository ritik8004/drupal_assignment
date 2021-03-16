@javascript @smoke @desktop @newPdp @mcaeuat @flsauat @aeoaeuat
Feature: Testing new PDP MetaData and Add to cart for desktop

  Background:
    Given I am on "{np_plp_product_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @aeoaeuat @mcaeuat
  Scenario: To verify behaviour of size drawer
    Then the element "#pdp-add-to-cart-form-main" should exist
    Then I should see a "#pdp-add-to-cart-form-main .cart-form-attribute.size" element on page
    And the element "#pdp-add-to-cart-form-main .magv2-size-btn-wrapper" should exist
    And the element "#product-quantity-dropdown" should exist
    When I click on ".magv2-size-btn-wrapper" element
    And I wait 5 seconds
    Then I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .non-group-anchor-wrapper label" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .size-guide a" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .non-group-option-wrapper #size" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .magv2-confirm-size-btn" element on page
    When I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".cart-form-attribute .magv2-select-popup-container " should not exist

  @language @aeoaeuat @mcaeuat
  Scenario: To verify behaviour of size drawer
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then the element "#pdp-add-to-cart-form-main" should exist
    Then I should see a "#pdp-add-to-cart-form-main .cart-form-attribute.size" element on page
    And the element "#pdp-add-to-cart-form-main .magv2-size-btn-wrapper" should exist
    And the element "#product-quantity-dropdown" should exist
    When I click on ".magv2-size-btn-wrapper" element
    And I wait 5 seconds
    Then I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .non-group-anchor-wrapper label" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .size-guide a" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .non-group-option-wrapper #size" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .magv2-confirm-size-btn" element on page
    When I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".cart-form-attribute .magv2-select-popup-container " should not exist

  @flsauat
  Scenario: To verify behaviour of size drawer
    Then the element "#pdp-add-to-cart-form-main" should exist
    Then I should see a "#pdp-add-to-cart-form-main .cart-form-attribute" element on page
    And the element "#pdp-add-to-cart-form-main .magv2-size-btn-wrapper" should exist
    And the element "#product-quantity-dropdown" should exist
    When I click on ".magv2-size-btn-wrapper" element
    And I wait 5 seconds
    Then I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper label" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a" element on page
    Then I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a:nth-child(1)" element
    Then the element ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a:nth-child(1)" having attribute "class" should contain "active"
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .size-guide a" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper #size_shoe_eu" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .magv2-confirm-size-btn button" element on page
    When I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".cart-form-attribute .magv2-select-popup-container " should not exist

  @flsauat @language
  Scenario: To verify behaviour of size drawer
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then the element "#pdp-add-to-cart-form-main" should exist
    Then I should see a "#pdp-add-to-cart-form-main .cart-form-attribute" element on page
    And the element "#pdp-add-to-cart-form-main .magv2-size-btn-wrapper" should exist
    And the element "#product-quantity-dropdown" should exist
    When I click on ".magv2-size-btn-wrapper" element
    And I wait 5 seconds
    Then I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper label" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a" element on page
    Then I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a:nth-child(1)" element
    Then the element ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper .group-anchor-links a:nth-child(1)" having attribute "class" should contain "active"
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .size-guide a" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .group-anchor-wrapper #size_shoe_eu" element on page
    And I should see a ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-content-wrapper .magv2-confirm-size-btn button" element on page
    When I click on ".cart-form-attribute .magv2-select-popup-container .magv2-select-popup-wrapper .magv2-select-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".cart-form-attribute .magv2-select-popup-container " should not exist
