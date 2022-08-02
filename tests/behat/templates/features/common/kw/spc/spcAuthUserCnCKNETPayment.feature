@javascript @KNET @KNetPayment @clickCollect @coskwuat @tbseguat @aeokwuat @bbwkwuat @bpkwuat @hmkwuat @vskwuat @tbskwuat @flkwuat @mckwuat @pbkwuat @pbkkwuat @westelmkwuat
Feature: SPC Checkout Click and Collect using KNET payment method for authenticated user

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @cnc @desktop @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 50 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    Then the Knet payment checkbox should be checked
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait 2 seconds
    And I press "Confirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I should save the order details in the file

  @cc @cnc @language @desktop @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 50 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "بنك اختبار كي نت [KNET1]" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form for arabic
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "إرسال"
    And I wait 2 seconds
    And I press "proceedConfirm"
    And I wait 5 seconds
    And I should save the order details in the file

  @cc @cnc @mobile @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 50 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    Then the Knet payment checkbox should be checked
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "بنك اختبار كي نت [KNET1]" from dropdown ".paymentselect"
    And I wait 2 seconds
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait 2 seconds
    And I press "Confirm"
    And I wait 5 seconds
    And I wait for the page to load
    And I should save the order details in the file
