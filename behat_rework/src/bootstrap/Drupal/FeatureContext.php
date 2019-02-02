<?php

namespace Alshaya\BehatContexts;

define("ORDER_ASC", 1);
define("ORDER_DSC", 0);

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Context\BehatContext;
use Behat\Mink;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\Element;
use Behat\Mink\WebAssert;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Drupal\DrupalExtension\Context\MinkExtension;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends CustomMinkContext {

  /**
   * @Given /^I wait for the page to load$/
   */
  public function iWaitForThePageToLoad() {
    $this->getSession()->wait(25000, '(0 === jQuery.active)');
  }

  /**
   * @When /^I close the popup$/
   */
  public function iCloseThePopup() {
    $page = $this->getSession()->getPage();
    $popup = $this->getSession()->getPage()->findById("popup");
    if ($popup->isVisible()) {
      $page->findById('close-popup')->click();
    } else {
      echo 'Welcome Popup is currently not displayed';
    }
  }

  /**
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
