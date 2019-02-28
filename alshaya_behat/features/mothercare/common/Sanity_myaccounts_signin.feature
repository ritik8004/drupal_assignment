@javascript
  Feature: To verify my account, Sign in functionality

    Scenario: As an authenticated user,
    I should be able to sign in after providing valid credentials and
      reset my password
      Given I am on homepage
      And I wait for the page to load
      When I close the popup
      And I wait for the page to load
      And I go to "/user/login"
      When I fill in "edit-name" with "karnika.jain+test@qed42.com"
      And I fill in "edit-pass" with "Password@1"
      And I press "sign in"
      Then I should see the link "My account"
      And I should see the link "Sign out"
      And I should see "recent orders"
      When I follow "Sign out"
      And I wait for AJAX to finish
      Then I should see "Sign in"
      And the url should match "/user/login"
      And I follow "Forgot password?"
      And the url should match "/user/password"
      When I fill in "edit-name" with "karnika.jain+test@qed42.com"
      And I press "Submit"
      Then I should see "Further instructions have been sent to your email address."
      And the url should match "/user/login"


    Scenario: As a guest user,
    I should not be able to sign in or reset password
      with invalid credentials
      Given I go to "user/login"
      When I press "sign in"
      And I wait for AJAX to finish
      Then I should see "Please enter your Email address."
      And I should see "Please enter your Password."
      When I fill in "edit-name" with "name@surname@gmail.com"
      And I press "sign in"
      And I wait 2 seconds
      Then I should see text matching "email address does not contain a valid email."
      And I follow "Forgot password?"
      When I fill in "edit-name" with "noemail@gmail.com"
      And I press "Submit"
      When I wait for the page to load
      Then I should see " is not recognized as a username or an email address."

    Scenario:
    As an authenticated user
    I should be able to see all the sections
    after logging in
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      And I wait for the page to load
      Then I should see the link "my account" in ".my-account-nav" section
      And I should see the link "orders" in ".my-account-nav" section
      Then I should see the link "contact details" in ".my-account-nav" section
      And I should see the link "address book" in ".my-account-nav" section
      And I should see the link "change password" in ".my-account-nav" section
      Then the "my account" tab should be selected

#    Scenario Outline:
#    As an authenticated user
#    I should be able to view the Need help section
#    and access the links under Need help
##      When I see the text "Need help with your order?"
#      Then I should see the link "Contact customer services"
#      Then I should see the link "Terms and Conditions of Sale"
#      And I should see the link "Delivery Information"
#      When I follow "<link>"
#      And I wait for the page to load
#      Then I should see "<text>"
#      And the url should match "<url>"
#    Examples:
#      |link|text|url|
#      |Contact customer services|Contact us|/contact|
#      |Terms and Conditions of Sale|Terms and Conditions of Sale|/terms-and-conditions-of-sale|
#      |Delivery Information |Delivery Information|/delivery-information|

    Scenario: As an authenticated user
    I should be able to update my contact details
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      And I wait for the page to load
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
      And I wait for the page to load
      When I fill in "edit-field-first-name-0-value" with "Aadya"
      When I fill in "edit-field-last-name-0-value" with "Sharma"
      When I fill in "edit-field-mobile-number-0-mobile" with "55004466"
      And I press "Save"
      And I wait for the page to load
      Then I should see "Aadya"
      And I should not see "55004455"
      And I should see "Contact details changes have been saved."
      Then I fill in "edit-field-first-name-0-value" with "Test"
      And I fill in "edit-field-last-name-0-value" with "Test"
      When I fill in "edit-field-mobile-number-0-mobile" with "55004466"
      And I press "Save"

    Scenario: As an authenticated user
    I should be able to add a new address
    to my address book
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
      And I wait for the page to load
      Then I get the total count of address blocks
      When I follow "Add new Address"
      And I wait for AJAX to finish
      When I fill in "field_address[0][address][given_name]" with "Test"
      And I fill in "field_address[0][address][family_name]" with "Test"
      When I fill in "field_address[0][address][mobile_number][mobile]" with "55004455"
      When I select "Sharq" from "field_address[0][address][administrative_area]"
      When I fill in "field_address[0][address][locality]" with "Block A"
      When I fill in "field_address[0][address][address_line1]" with "Street B"
      When I fill in "field_address[0][address][dependent_locality]" with "Sanyogita Apartment"
      When I fill in "field_address[0][address][address_line2]" with "5"
      And I press "add address"
      When I wait for AJAX to finish
      And I wait for the page to load
      Then I should see "Address is added successfully"
      And the new address block should be displayed on address book


    Scenario: As an authenticated user
    I should be able to perform Need help with your order? action on add/edit address pages
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
      And I wait for the page to load
      When I follow "Add new Address"
      And I wait for AJAX to finish
      When I follow "cancel"
      And I wait for the page to load
      Then I should not see the text "First Name"
      When I click Edit Address
      And I wait for AJAX to finish
      When I follow "cancel"
      And I wait for the page to load
      Then I should not see the text "First Name"


    Scenario: As an authenticated user
    I should be able to edit an address
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
      And I wait for the page to load
      When I click Edit Address
      And I wait for AJAX to finish
      When I select "Abbasiya" from "field_address[0][address][administrative_area]"
      When I fill in "field_address[0][address][address_line2]" with "2"
      And I press "Save"
      When I wait for the page to load
      Then I should see "Address is updated successfully."


    Scenario: As an authenticated user
    I should see the options to change my password
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
      And I wait for the page to load
      Then I should see "Change Password"
      Then I should see "current password"
      And I should see "new password"
      Then I should see the button "change password"
      When I fill in "edit-pass" with ""
      And I wait 2 seconds
      Then I should see text matching "Your password must have at-least 7 characters."
      Then I should see text matching "Your password must contain at-least 1 special character."
      Then I should see text matching "Your password must contain at-least 1 numeric character."
      Then I should see text matching "Spaces are not allowed in your password."
      Then I should see text matching "The previous four passwords are not allowed."
      When I press "change password"
      Then I should see "Please enter your current password."
      And I should see "Please enter your new password."

    Scenario: As an authenticated user
    I should be able to view breadcrumbs on My Account section
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      And I wait for the page to load
      Then the breadcrumb "home > my account" should be displayed
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
      And I wait for the page to load
      Then the breadcrumb "home > my account > orders" should be displayed
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
      And I wait for the page to load
      Then the breadcrumb "home > my account > contact details" should be displayed
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
      And I wait for the page to load
      Then the breadcrumb "home > my account > address book" should be displayed
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
      And I wait for the page to load
      Then the breadcrumb "home > my account > change password" should be displayed

    Scenario Outline: As a guest
    I should be able to view breadcrumbs across the site
      Given I am on "<page>"
      And I wait for the page to load
      Then the breadcrumb "<breadcrumb>" should be displayed
    Examples:
      |page|breadcrumb|
      |/baby-clothing|home > baby clothing |
      |/baby-clothing/baby-newborn-18-months/bodysuits|home > baby clothing > baby (newborn - 18 months) > bodysuits|
     # |/animal-bodysuit-2-pack|home > baby clothing > baby (newborn - 18 months) > bodysuits > animal bodysuit - 2 pack|
      |/cart                                                 |home > basket                                                                                            |
      |/store-finder                                         |home > find stores                                                                                       |


    Scenario: As an authenticated user
    I should not be able to delete my primary address
    but I should be able to delete any other address
      Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
      When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
      And I wait for the page to load
      Then I should not see the delete button for primary address
      When I follow "Delete"
      And I wait for AJAX to finish
      When I press "No, take me back"
      Then I should see "Address book"
      Then I get the total count of address blocks
      When I follow "Delete"
      And I wait for AJAX to finish
      When I confirm deletion of address
      And I wait for AJAX to finish
      Then I should see "Address is deleted successfully."
      And the address block should be deleted from address book

