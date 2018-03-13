@javascript
Feature: As a user I should be able to
  complete Email Sign up on VS site

 Scenario:Email signup
   Given I am on "/email-sign-up"
   And I wait 5 seconds
   Then I should see "Be the first to know"
   And I fill in "edit-first-name" with "test"
   And I fill in "edit-last-name" with "user"
   And I fill in "Mobile number" with "55667799"
   Then I enter a valid Email ID in field "edit-email"
   And I press "Submit"
   And I wait 5 seconds
   Then I should see the success message "Thank you :)"