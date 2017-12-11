@javascript @loyalty @manual @mmcpa-2352
Feature: Test the privilege card functionality for returning customer

  Scenario: As a returning customer
    no PC number should be displayed on Order confirmation page
    when the PC number is null on basket
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for the page to load
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    When I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    Then I should not see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 0013 - 5844"

  Scenario: As a returning customer
    PC number from the basket should be displayed on Order confirmation page when my account is null
    and my account field for PC should remain null after placing order
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for the page to load
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    When I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    Then I should see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 0013 - 5844"
    When I am on homepage
    And I wait for the page to load
    When I follow "My account"
    And I wait for the page to load
    Then I should not see "6362 - 5440 - 0013 - 5844"

  Scenario: As a returning customer
    no PC number should be displayed on order confirmation page when basket PC number is null
    and my account PC number is not null
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for the page to load
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    When I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    Then I should not see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 0013 - 5844"

  Scenario: As a returning customer
  PC number on basket should get updated with My account number (basket = null)
  when user returns to basket page from any of the checkout pages
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "Back to basket"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    Then I should see value "6362-5440-0013-5844" for element "#edit-privilege-card-number"

  Scenario: As a returning customer
    value from basket page should be displayed on Order confirmation page
    even when user has different PC number in his account
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    Then I should see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 1511 - 8942"

  Scenario: As a returning customer
  value on the basket should prevail when user returns to basket from checkout pages
  and user had a different value in my account section
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "Back to basket"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    Then I should see value "6362-5440-1511-8942" for element "#edit-privilege-card-number"

  @arabic
  Scenario: As a returning customer
  no PC number should be displayed on Order confirmation page
  when the PC number is null on basket
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should not see text matching " رقم بطاقة نادي الامتيازات: 6362 - 5440 - 0013 - 5844"

  @arabic
  Scenario: As a returning customer
  PC number from the basket should be displayed on Order confirmation page when my account is null
  and my account field for PC should remain null after placing order
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    And I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see text matching " رقم بطاقة نادي الامتيازات: 6362 - 5440 - 0013 - 5844"
    When I go to "/ar"
    And I wait for the page to load
    When I follow "حسابي"
    And I wait for the page to load
    Then I should not see "6362 - 5440 - 0013 - 5844"

  @arabic
  Scenario: As a returning customer
  no PC number should be displayed on order confirmation page when basket PC number is null
  and my account PC number is not null
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    And I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should not see text matching " رقم بطاقة نادي الامتيازات: 6362 - 5440 - 0013 - 5844"

  @arabic
  Scenario: As a returning customer
  PC number on basket should get updated with My account number (basket = null)
  when user returns to basket page from any of the checkout pages
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "العودة إلى حقيبة التسوق"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    Then I should see value "6362-5440-0013-5844" for element "#edit-privilege-card-number"

  @arabic
  Scenario: As a returning customer
  value from basket page should be displayed on Order confirmation page
  even when user has different PC number in his account
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    And I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see text matching " رقم بطاقة نادي الامتيازات: 6362 - 5440 - 1511 - 8942"

  @arabic
  Scenario: As a returning customer
  value on the basket should prevail when user returns to basket from checkout pages
  and user had a different value in my account section
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I follow "Sign out"
    And I wait for the page to load
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "العودة إلى حقيبة التسوق"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    Then I should see value "6362-5440-1511-8942" for element "#edit-privilege-card-number"