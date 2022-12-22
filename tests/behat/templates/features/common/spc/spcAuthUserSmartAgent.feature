@javascript @auth @smartAgent
Feature: SPC to verify Smart Agent user functionality for authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_smart_agent_email}" with password "{spc_smart_agent_password}"
    And I wait for element "#block-page-title"
    Then I should be on homepage

  Scenario: As an Authenticated user, I should be able to verify the smart agent checkout functionality
    Given the element ".smart-agent-header-wrapper" should exist
    And the element ".agent-logged-in" should exist
    And the element ".smart-agent-header-wrapper .smart-agent-logout-link" should exist
    When I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    When I click jQuery ".share-options .share-option.wa span.label" element on page
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait for element "#smart-agent-share-mobile-error"
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "Please enter valid mobile number."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I press "Share"
    And I wait for AJAX to finish
    And I click jQuery ".share-options .share-option.email span.label" element on page
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait for AJAX to finish
    Then the element "#smart-agent-share-email-error" should exist
    And I should see "Please enter your email address."
    And I fill in "smart-agent-share-email" with "{anon_email}"
    And I press "Share"
    And I wait for AJAX to finish
    And I click jQuery ".share-options .share-option.sms span.label" element on page
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait for AJAX to finish
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "Please enter valid mobile number."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I press "Share"
    And I wait for AJAX to finish
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    And I am logged in as an authenticated user "{spc_returning_user_email}" with password "{spc_returning_user_password}"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    And I click on ".smart-agent-end-transaction" element
    And I wait for the page to load
    Then I should see an ".smart-agent-header-wrapper" element
    When I click on ".smart-agent-logout-link" element
    And I wait for AJAX to finish
    Then I should not see an ".smart-agent-header-wrapper" element

  @language
  Scenario: As an Authenticated user, I should be able to verify the smart agent checkout functionality in second language
    When I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"
    When I follow "{language_link}"
    And I wait for the page to load
    Given the element ".smart-agent-header-wrapper" should exist
    And the element ".agent-logged-in" should exist
    And the element ".smart-agent-header-wrapper .smart-agent-logout-link" should exist
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    When I click jQuery ".share-options .share-option.wa span.label" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "مشاركة السلة مع العميل"
    And I press "إرسال"
    And I wait 10 seconds
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "يرجى إدخال رقم الجوال صحيح."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I wait 5 seconds
    And I press "إرسال"
    And I wait 10 seconds
    And I click jQuery ".share-options .share-option.email span.label" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "مشاركة السلة مع العميل"
    And I press "إرسال"
    And I wait 10 seconds
    Then the element "#smart-agent-share-email-error" should exist
    And I should see "يرجى إدخال عنوان البريد الإلكتروني"
    And I fill in "smart-agent-share-email" with "{anon_email}"
    And I wait 5 seconds
    And I press "إرسال"
    And I wait 5 seconds
    And I click jQuery ".share-options .share-option.sms span.label" element on page
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "مشاركة السلة مع العميل"
    And I press "إرسال"
    And I wait for AJAX to finish
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "يرجى إدخال رقم الجوال صحيح."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I wait 5 seconds
    And I press "إرسال"
    And I wait 5 seconds
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    And I am logged in as an authenticated user "{spc_returning_user_email}" with password "{spc_returning_user_password}"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    And I click on ".smart-agent-end-transaction" element
    And I wait for the page to load
    Then I should see an ".smart-agent-header-wrapper" element
    When I click on ".smart-agent-logout-link" element
    And I wait for AJAX to finish
    Then I should not see an ".smart-agent-header-wrapper" element

  @mobile
  Scenario: As an Authenticated user, I should be able to verify the smart agent checkout functionality for mobile
    When I am on "{spc_basket_page}"
    And I wait 5 seconds
    Given the element ".smart-agent-header-wrapper" should exist
    And the element ".agent-logged-in" should exist
    And the element ".smart-agent-header-wrapper .smart-agent-logout-link" should exist
    When I am on "{spc_basket_page}"
    And I wait 5 seconds
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    When I click jQuery ".share-options .share-option.wa span.label" element on page
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait 5 seconds
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "Please enter valid mobile number."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I wait 5 seconds
    And I press "Share"
    And I wait 10 seconds
    And I click jQuery ".share-options .share-option.email span.label" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait 5 seconds
    Then the element "#smart-agent-share-email-error" should exist
    And I should see "Please enter your email address."
    And I fill in "smart-agent-share-email" with "{anon_email}"
    And I wait 5 seconds
    And I press "Share"
    And I wait 10 seconds
    And I click jQuery ".share-options .share-option.sms span.label" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    Then the element ".popup-overlay.smart-agent-share-modal-overlay" should exist
    And I should see the text "Share basket with customer"
    And I press "Share"
    And I wait 10 seconds
    Then the element "#smart-agent-share-mobile-error" should exist
    And I should see "Please enter valid mobile number."
    And I fill in "smart-agent-share-mobile" with "{mobile}"
    And I press "Share"
    And I wait 5 seconds
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    And I am logged in as an authenticated user "{spc_returning_user_email}" with password "{spc_returning_user_password}"
    And the element ".smart-agent-session-banner" should exist
    And the element ".spc-checkout-smart-agent-share-message" should exist
    And the element "div.share-options" should exist
    And I click on ".smart-agent-end-transaction" element
    And I wait for the page to load
    Then I should see an ".smart-agent-header-wrapper" element
    When I click on ".smart-agent-logout-link" element
    And I wait for AJAX to finish
    Then I should not see an ".smart-agent-header-wrapper" element
