@javascript @loyalty @manual @mmcpa-2352
Feature: Test privilege card features for Guest

  Scenario: As a Guest
    no PC number should be displayed on Order Confirmation page
    when there is no value on basket page and loyalty block details should appear
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When follow "checkout as guest"
    And I wait for the page to load
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should not see text matching "Your PRIVILEGES CLUB card number is:"
    Then I should see "Join the club"
    And I should see "Win exciting prizes"
    Then I should see "Unlock exclusive rewards"
    And I should see "Be the first to know"
    Then I should see the link "Learn more"

  Scenario: As a Guest
    PC number from the basket should be displayed on Order confirmation page
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    And I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
    And I wait for the page to load
    When  I follow "checkout as guest"
    And I wait for the page to load
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
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

  @arabic
  Scenario: As a Guest on Arabic site
  no PC number should be displayed on Order Confirmation page
  when there is no value on basket page and loyalty block details should appear
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should not see text matching "رقم بطاقة نادي الامتيازات:"
    Then I should see "نادي الامتيازات"
    Then I should see "احصل على مكافآت حصرية"
    And I should see "كُن أول من يعلم"
    Then I should see the link "مزيد من المعلومات"

  @arabic
  Scenario: As a Guest
  PC number from the basket should be displayed on Order confirmation page
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/ar/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    And I fill in "edit-privilege-card-number2" with "015118942"
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When  I follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_checkmo"
    And I wait for AJAX to finish
    And I accept terms and conditions
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see text matching " رقم بطاقة نادي الامتيازات: 6362 - 5440 - 1511 - 8942"