@javascript @KNetPayment @homeDelivery @auth @coskwuat @aeokwuat @bbwkwuat @tbseguat @bpkwuat @hmkwuat @vskwuat @tbskwuat @flkwuat @mckwuat @mujikwuat @pbkwuat @pbkkwuat @westelmkwuat
Feature: SPC Checkout Home Delivery Knet Payment for Authenticated User

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I go to in stock category page
    And I wait for element "#block-page-title"

  @cc @hd @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET Payment
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    And I wait for element "#paypage"
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I wait for AJAX to finish
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait for element "#payConfirm"
    And I press "Confirm"
    And I wait for element "#block-page-title"
    And I wait for the page to load
    And I should save the order details in the file

  @cc @hd @language @desktop @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET payment in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    And I wait for element "#paypage"
    And I select "بنك اختبار كي نت [KNET1]" from dropdown ".paymentselect"
    And I wait for AJAX to finish
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form for arabic
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "إرسال"
    And I wait for element "#payConfirm"
    And I press "proceedConfirm"
    And I wait for element "#block-page-title"
    And I wait for the page to load
    And I should save the order details in the file

  @cc @hd @language @mobile @Knet
  Scenario: As an authenticated user, I should be able to checkout using KNET payment in second language
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait for the page to load
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    And I wait for element "#paypage"
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I wait for AJAX to finish
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait for element "#payConfirm"
    And I press "Confirm"
    And I wait for element "#block-page-title"
    And I wait for the page to load
    And I should save the order details in the file
