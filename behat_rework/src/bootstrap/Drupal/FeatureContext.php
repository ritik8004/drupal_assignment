<?php

namespace Alshaya\BehatContexts;

define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends CustomMinkContext {

  /**
   * Wait for page to load for 25 seconds.
   *
   * @Given /^I wait for the page to load$/
   */
  public function iWaitForThePageToLoad() {
    $this->getSession()->wait(25000, '(0 === jQuery.active)');
  }

  /**
   * Close the popup.
   *
   * @When /^I close the popup$/
   */
  public function iCloseThePopup() {
    $page = $this->getSession()->getPage();
    $popup = $this->getSession()->getPage()->findById("popup");
    if ($popup->isVisible()) {
      $page->findById('close-popup')->click();
    }
    else {
      echo 'Welcome Popup is currently not displayed';
    }
  }

  /**
   * Validate given breadcrumb exists on current page.
   *
   * @Then the breadcrumb :arg1 should be displayed
   */
  public function theBreadcrumbShouldBeDisplayed($breadcrumb) {
    $page = $this->getSession()->getPage();
    $breadcrumb_elements = $page->findAll('css', '#block-breadcrumbs > nav > ol > li');
    $actual_breadcrumb = [];
    foreach ($breadcrumb_elements as $element) {
      $actual_breadcrumb[] = $element->find('css', 'a')->getText();
    }
    $actual_breadcrumb_result = implode(' > ', $actual_breadcrumb);
    if ($breadcrumb !== $actual_breadcrumb_result) {
      throw new \Exception('Incorrect breadcrumb displayed');
    }
  }

}
