@javascript @smoke @guest @hmsauat @hmaeuat @coskwlocal
Feature: To verify the mobile OTP functionality on COD payment method for Guest user

  Background:
    Given I visit "{spc_pdp_page}"
    And I wait for the page to load
    And I wait for AJAX to finish

  @valid-otp @desktop
  # @todo Check if can also work on mobile test
  Scenario: As a Guest user, I should be able to validate the mobile OTP for COD payment method
    # @todo: Find a way to detect when a full loader is on the page and the element is not clickable
    When I click on Add-to-cart button
    And I wait for element "#mini-cart-wrapper a.cart-link .quantity"
    Then I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "checkout as guest"
    And I wait for element "#spc-checkout .home-delivery"
    Then I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-payment-methods .payment-method-cashondelivery" element on page
    Then I wait for element ".cashondelivery .cod-mobile-otp"
    And I wait for AJAX to finish
    And I wait 2 seconds
    Given the mobile OTP is verified
    Then I should see an ".cod-mobile-otp__verified" element
