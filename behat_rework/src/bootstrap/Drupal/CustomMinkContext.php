<?php

namespace Alshaya\BehatContexts;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * A context for working with blocks and the core block system.
 */
class CustomMinkContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * Contains an array of parameters passed with profile suites.
   *
   * @var array
   */
  protected $parameter_bag = [];

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct($parameters = []) {
    $this->parameter_bag = $parameters;
  }

  /**
   * @return mixed
   */
  public function getParameterBag() {
    return $this->parameter_bag;
  }

  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope)  {
    $environment = $scope->getEnvironment();
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Fills in form field with specified id|name|label|value
   * Example: When I fill in dynamic field "username" with: "bwayne"
   * Example: And I fill in dynamic field "bwayne" for "username"
   *
   * @When /^(?:|I )fill in field "(?P<field>(?:[^"]|\\")*)" with dynamic "(?P<value>(?:[^"]|\\")*)"$/
   * @When /^(?:|I )fill in field "(?P<field>(?:[^"]|\\")*)" with dynamic:$/
   * @When /^(?:|I )fill in dynamic "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
   */
  public function iFillInDynamicFieldWith($field, $value) {
    preg_match('/{([^}]*)}/', $value, $matches);
    $value = !empty($matches) && !empty($this->parameter_bag[$matches[1]])
      ? $this->parameter_bag[$matches[1]]
      : $value;

    $this->minkContext->fillField($field, $value);
  }

}