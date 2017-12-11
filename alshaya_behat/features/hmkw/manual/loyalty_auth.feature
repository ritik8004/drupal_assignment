@javascript @loyalty @manual @mmcpa-2352
Feature:
  Test all the scenarios related to privilege card number for authenticated user

  Background:
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load

  Scenario: As an authenticated user
    privilege card number should not be displayed on order confirmation page
    when the field is empty in both my account section and basket page
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    Then I should not see "6362 - 5440 - 0013 - 5844"
    When I press "checkout securely"
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
    Then I should not see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 0013 - 5844"

  Scenario: As an authenticated user
    PC value from the basket should be displayed on Order confirmation page when my account is null
    and my account value should not get updated with the basket PC value
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    And I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
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
    When I am on homepage
    And I wait for the page to load
    When I follow "My account"
    And I wait for the page to load
    Then I should not see "6362 - 5440 - 1511 - 8942"

  Scenario: As an authenticated user
    no PC number should be displayed on Order confirmation page
    when the basket value is edited to be null, but my account has a PC number
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with "000135844"
    When I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "checkout securely"
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
    Then I should not see text matching "Your PRIVILEGES CLUB card number is: 6362 - 5440 - 0013 - 5844"
    When I am on homepage
    And I wait for the page to load
    When I follow "My account"
    And I wait for the page to load
    Then I should not see "Join the club"
    Then I should see "6362 - 5440 - 0013 - 5844"

  Scenario: As an authenticated user
    PC value from the basket should be displayed on order confirmation page
    and not from the my account section
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with "000135844"
    When I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
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
    When I am on homepage
    And I wait for the page to load
    When I follow "My account"
    And I wait for the page to load
    Then I should not see "Join the club"
    Then I should see "6362 - 5440 - 0013 - 5844"

  @arabic
  Scenario: As an authenticated user on Arabic site
  privilege card number should not be displayed on order confirmation page
  when the field is empty in both my account section and basket page
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    Then I should not see "6362 - 5440 - 0013 - 5844"
    When I press "إتمام الشراء بأمان"
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
  Scenario: As an authenticated user on Arabic site
  PC value from the basket should be displayed on Order confirmation page when my account is null
  and my account value should not get updated with the basket PC value
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    And I fill in "edit-privilege-card-number2" with "015118942"
    When I press "إتمام الشراء بأمان"
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
    When I go to "/ar"
    And I wait for the page to load
    When I follow "حسابي"
    And I wait for the page to load
    Then I should not see "6362 - 5440 - 1511 - 8942"

  @arabic
  Scenario: As an authenticated user
  no PC number should be displayed on Order confirmation page
  when the basket value is edited to be null, but my account has a PC number
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with "000135844"
    When I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with ""
    When I press "إتمام الشراء بأمان"
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
    When I go to "/ar"
    And I wait for the page to load
    When I follow "حسابي"
    And I wait for the page to load
    Then I should not see "اربح جوائز مدهشة"
    Then I should see "6362 - 5440 - 0013 - 5844"

  @arabic
  Scenario: As an authenticated user
  PC value from the basket should be displayed on order confirmation page
  and not from the my account section
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with ""
    When I click the label for "#ui-id-2 > p.title"
    And I fill in "edit-privilege-card-number" with "000135844"
    When I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I am on a simple product page
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    When I fill in "edit-privilege-card-number2" with "015118942"
    When I press "إتمام الشراء بأمان"
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
    When I go to "/ar"
    And I wait for the page to load
    When I follow "حسابي"
    And I wait for the page to load
    Then I should not see "Join the club"
    Then I should see "6362 - 5440 - 0013 - 5844"
