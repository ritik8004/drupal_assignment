@javascript
Feature: As a customer
  I should be able to checkout
  using various payment options

  Background:
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    Then I should see the link "My account"
    Given I am on a simple product
    And I wait for AJAX to finish
    When I press "add to cart"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    And I remove promo panel
    And I press "checkout securely"
    And I wait for the page to load

  @hd @cod
  Scenario: As an authenticated user
  I should be able to checkout using Home delivery
  and pay by Cash-on-delivery
    When I follow "Home delivery"
    And I wait for AJAX to finish
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to the "#edit-actions-next" element
    And I remove promo panel from delivery page
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @hd @cs
  Scenario: As an authenticated user
    I should be able to checkout on HD
    using Cybersource payment method
    When I follow "Home delivery"
    And I wait for AJAX to finish
    And I remove promo panel from delivery page
    And I scroll to x "100" y "400" coordinates of page
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to the "#edit-actions-next" element
    And I press "proceed to payment"
    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    And I scroll to x "100" y "400" coordinates of page
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    And I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "