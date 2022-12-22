@javascript @auth @e-gift @hmkwuat @pbkwuat @bbwkwuat @bbwaeuat @bbwsauat @vskwuat @vssauat @vsaeuat @flkwuat @flsauat @flaeuat @coskwuat @cossauat @cosaeuat @hmsauat @hmaeuat @pbkkwuat @pbksauat @pbkaeuat @tbskwuat @tbseguat @mujikwuat @mujisauat @mujiaeuat @pbsauat @pbaeuat @mckwuat @aeokwuat @bpkwuat @westelmkwuat
Feature: SPC Checkout Egift feature for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_egift_page}"
    And I wait for the page to load

  @e-gift
  Scenario: As an Authenticated user, I should be able to see the E-gift section on the site
    And I should see an "#block-content .c-content div.paragraph-image_title_subtitle_link" element
    And I should see an "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:first-child a" element
    And I should see an "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:nth-child(2) a" element
    And I should see an "#check-balance-button" element
    And I should see an ".c-content__container a" element
    And I should see an ".field--name-field-promo-blocks div.c-accordion #ui-id-1" element

  @egift-card-purchase
  Scenario: As an authenticated user, I should be able to use E-gift feature on the site
    And I click on "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:nth-child(2) a" element
    And I wait for element "#egift-card-purchase-wrapper"
    And I should see an "#block-page-title h1.c-page-title" element
    And I should see an "div.egift-list-wrapper" element
    And I click on ".egift-card-purchase-config-wrapper div.egift-list-wrapper li.card-thumbnail-image:first-child" element
    And I wait for element "li.card-thumbnail-image.active:first-child"
    And I should see an ".egift-card-amount-list-wrapper" element
    And I click on ".egift-card-amount-list-wrapper li.item-amount:first-child" element
    And I wait for element ".egift-card-amount-list-wrapper li.item-amount.active:first-child"
    And I should see an ".egift-open-amount-wrapper" element
    And I should see an "#egift-card-purchase-wrapper #egift-purchase-form" element
    And I should see an "#egift-card-purchase-wrapper .step-two-fields #egiftFor-friends-family" element
    And I fill in "egift-recipient-name" with "Test"
    And I fill in "egift-recipient-email" with "nikita@axelerant.com"
    And I fill in "egift-message" with "Egift purchase card."
    And I click on ".action-buttons .egift-purchase-add-to-cart-button" element
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I should see an ".spc-content .spc-cart-items .egift-product-title" element
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I should see an ".redeem-egift-card" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @egift-card-link
  Scenario: As an authenticated user, I should be able to use Link Account option for Egift feature
    When I click on "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:first-child a" element
    And I wait for element "#my-egift-card"
    And I should see an "#block-alshayamyaccountlinks a.my-account-egift-card" element
    And I click jQuery "#block-alshayamyaccountlinks .my-account-egift-card" element on page
    And I wait for AJAX to finish
    And I wait for element "#egift-redeem-get-code-button"
    And I fill in "egift-card-number" with "6362"
    And I click on "#egift-redeem-get-code-button" element
    And I wait for AJAX to finish
    And I wait for element "#egift-card-number-error"
    And I fill in "egift-card-number" with "6362543017758390"
    And I click on "#egift-redeem-get-code-button" element
    And I wait for AJAX to finish
    And I wait for element ".egift-card-verify-code"
    And I fill in "otp-code" with "4884"
    And I click on "#egift-redeem-button" element
    And I wait for AJAX to finish
    Then I should see an ".egift-verify-code #egift-code-error" element

  @egift-card-balance
  Scenario: As an authenticated user, I should be able to check the Egift card balance
    When I click on "#check-balance-button" element
    And I wait for element ".egift-check-bal-title"
    Then I should see an "div.form-wrapper #egift-balance-check-form" element
    And I fill in "egift_card_number" with "6362543018020808"
    And I click on "div.form-wrapper #egift-button" element
    And I wait for AJAX to finish
    And I wait for element "#egift_card_otp"
    Then I should see an "div.form-wrapper #egift_card_otp" element
    And I fill in "egift_card_otp" with "4884"
    And I click on "div.form-wrapper #egift-button" element
    And I wait for element "#egift_card_otp_error"
    Then I should see an "#egift_card_otp_error" element

  @egift-topupcard
  Scenario: As an authenticated user, I should be able to use Top up card feature on Egift page
    When I click on ".c-content__container a" element
    And I wait for element "#egift-topup-wrapper"
    Then I should be on "en/egift-card/topup"
    And I should see an "#block-page-title .c-page-title" element
    And I fill in "card_number" with "6362543018020808"
    And I click on ".egift-card-amount-list-wrapper li.item-amount:first-child" element
    And I click on "div.action-buttons" element
    And I wait for element "#delivery-method-home_delivery"
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @language @desktop
  Scenario: As an Authenticated user, I should be able to see the E-gift section on the arabic page
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And I should see an "#block-content .c-content div.paragraph-image_title_subtitle_link" element
    And I should see an "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:first-child a" element
    And I should see an "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:nth-child(2) a" element
    And I should see an "#check-balance-button" element
    And I should see an ".c-content__container a" element
    And I should see an ".field--name-field-promo-blocks div.c-accordion #ui-id-1" element

  @language @desktop @egift-card-purchase
  Scenario: As an authenticated user, I should be able to use E-gift feature on the arabic page
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    And I click on "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:nth-child(2) a" element
    And I wait for element "#egift-card-purchase-wrapper"
    And I should see an "#block-page-title h1.c-page-title" element
    And I should see an "div.egift-list-wrapper" element
    And I click on ".egift-card-purchase-config-wrapper div.egift-list-wrapper li.card-thumbnail-image:first-child" element
    And I wait for element "li.card-thumbnail-image.active:first-child"
    And I should see an ".egift-card-amount-list-wrapper" element
    And I click on ".egift-card-amount-list-wrapper li.item-amount:first-child" element
    And I wait for element ".egift-card-amount-list-wrapper li.item-amount.active:first-child"
    And I should see an ".egift-open-amount-wrapper" element
    And I should see an "#egift-card-purchase-wrapper #egift-purchase-form" element
    And I should see an "#egift-card-purchase-wrapper .step-two-fields #egiftFor-friends-family" element
    And I fill in "egift-recipient-name" with "Test"
    And I fill in "egift-recipient-email" with "nikita@axelerant.com"
    And I fill in "egift-message" with "Egift purchase card."
    And I click on ".action-buttons .egift-purchase-add-to-cart-button" element
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    Then I should see an ".spc-content .spc-cart-items .egift-product-title" element
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I should see an ".redeem-egift-card" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist

  @language @desktop @egift-card-link
  Scenario: As an authenticated user, I should be able to use Link Account option for Egift feature for second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I click on "#block-content .paragraph_banner_block .paragraph_banner_buttons .field__item:first-child a" element
    And I wait for element "#my-egift-card"
    And I should see an "#block-alshayamyaccountlinks .my-account-بطاقة-الهدايا" element
    And I click jQuery "#block-alshayamyaccountlinks .my-account-بطاقة-الهدايا" element on page
    And I wait for AJAX to finish
    And I wait for element "#egift-redeem-get-code-button"
    And I fill in "egift-card-number" with "6362"
    And I click on "#egift-redeem-get-code-button" element
    And I wait for AJAX to finish
    And I wait for element "#egift-card-number-error"
    And I fill in "egift-card-number" with "6362543017758390"
    And I click on "#egift-redeem-get-code-button" element
    And I wait for AJAX to finish
    And I wait for element ".egift-card-verify-code"
    And I fill in "otp-code" with "4884"
    And I click on "#egift-redeem-button" element
    And I wait for AJAX to finish
    Then I should see an ".egift-verify-code #egift-code-error" element

  @language @desktop @egift-card-balance
  Scenario: As an authenticated user, I should be able to check the Egift card balance for second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I click on "#check-balance-button" element
    And I wait for element ".egift-check-bal-title"
    Then I should see an "div.form-wrapper #egift-balance-check-form" element
    And I fill in "egift_card_number" with "6362543018020808"
    And I click on "div.form-wrapper #egift-button" element
    And I wait for AJAX to finish
    And I wait for element "#egift_card_otp"
    Then I should see an "div.form-wrapper #egift_card_otp" element
    And I fill in "egift_card_otp" with "4884"
    And I click on "div.form-wrapper #egift-button" element
    And I wait for element "#egift_card_otp_error"
    Then I should see an "#egift_card_otp_error" element

  @language @desktop @egift-topupcard
  Scenario: As an authenticated user, I should be able to use Top up card feature on Egift page for second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I click on ".c-content__container a" element
    And I wait for element "#egift-topup-wrapper"
    Then I should be on "en/egift-card/topup"
    And I should see an "#block-page-title .c-page-title" element
    And I fill in "card_number" with "6362543018020808"
    And I click on ".egift-card-amount-list-wrapper li.item-amount:first-child" element
    And I click on "div.action-buttons" element
    And I wait for element "#delivery-method-home_delivery"
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then the element ".discount-total" should exist
