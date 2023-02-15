@javascript @KNET @KNetPayment @clickCollect @coskwuat @aeokwuat @bbwkwuat @bpkwuat @tbseguat @hmkwuat @vskwuat @tbskwuat @flkwuat @mckwuat @pbkwuat @pbkkwuat @westelmkwuat
Feature: SPC Checkout Click and Collect using KNET payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @cc @cnc @desktop @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    When I click the anchor link ".edit-checkout-as-guest" on page
    # Wait and select the Click and Collect delivery method in checkout page.
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I wait for element ".checkout-link.submit"
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait for element ".paymentselect"
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I wait for element "#dcprefix option[value={spc_Knet_card_prefix}]"
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait for element "#proceedConfirm"
    And I press "Confirm"
    And I wait for element ".spc-order-summary-order-detail .spc-value"
    And I should save the order details in the file

  @cc @cnc @language @desktop @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait for element ".paymentselect"
    And I select "بنك اختبار كي نت [KNET1]" from dropdown ".paymentselect"
    And I wait for element "#dcprefix option[value={spc_Knet_card_prefix}]"
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form for arabic
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "إرسال"
    And I wait for element "#proceedConfirm"
    And I press "proceedConfirm"
    And I wait for element ".spc-order-summary-order-detail .spc-value"
    And I should save the order details in the file

  @cc @cnc @mobile @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait for element ".paymentselect"
    And I select "بنك اختبار كي نت [KNET1]" from dropdown ".paymentselect"
    And I wait for element "#dcprefix option[value={spc_Knet_card_prefix}]"
    Then I fill in "debitNumber" with "{spc_Knet_card}"
    And I select date and month in the form
    And I fill in "cardPin" with "{spc_Knet_pin}"
    And I press "Submit"
    And I wait for element "#proceedConfirm"
    And I press "Confirm"
    And I wait for element ".spc-order-summary-order-detail .spc-value"
    And I should save the order details in the file
