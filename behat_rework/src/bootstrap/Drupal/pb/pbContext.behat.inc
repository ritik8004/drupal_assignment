<?php

namespace Alshaya\BehatContexts;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\ElementNotFoundException;
use Exception;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Mink\Element\Element;
use Drupal\DrupalExtension\Context\MinkContext;
use Behat\Mink\WebAssert;
use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Example subcontext.
 */
class pbContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  protected $drupalContext;

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * @Then /^I should be able to see the header$/
   */
  public function iShouldBeAbleToSeeTheHeader()
  {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('Create An Account');
    if ($create_account == NULL) {
      throw new \Exception('Link for create an account is missing in the header');
    }

    $sign_in = $page->hasLink('Sign In');
    if ($sign_in == NULL) {
      throw new \Exception('Link for email sign in is missing in the header');
    }

    $find_store = $page->hasLink('Find A Store');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('عربية');
    if ($language == NULL) {
      throw new \Exception('Link to switch to Arabic language is missing in the header');
    }

    $free_design = $page->hasLink('Free Design Services');
    if ($free_design == NULL) {
      throw new \Exception('Link for Free Design Services is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer$/
   */
  public function iShouldBeAbleToSeeTheFooter()
  {
    $page = $this->getSession()->getPage();
    $termsandconditions = $page->find('css', '.c-footer-primary__region');
    $termsandconditions->hasLink('Privacy Policy' and 'Terms & Conditions');
    if ($termsandconditions == NULL) {
      throw new \Exception('PRIVACY and TERMS & CONDITIONS links are missing in the footer');
    }

    $copyright1 = '© 2020 Williams-Sonoma, Inc. All Rights Reserved';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);
    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $connect1 = 'Connect with us';
    $connect = $page->find('css', '.c-footer-primary__region');
    $connect->has('named', ['content', $connect1]);
    if ($connect == NULL) {
      throw new \Exception('Connect with us section is missing');
    }
    $facebook = $page->hasLink('Facebook');
    if ($facebook == NULL) {
      throw new \Exception('Link to facebook is missing in the footer');
    }
    $instagram = $page->hasLink('Instagram');
    if ($instagram == NULL) {
      throw new \Exception('Link to instagram is missing in the footer');
    }
    $youtube = $page->hasLink('Youtube');
    if ($youtube == NULL) {
      throw new \Exception('Link to Youtube is missing in the footer');
    }

//    $findstore1 = 'FIND YOUR NEAREST STORE';
//    $findstore = $page->find('css', '.region-footer-primary');
//    $findstore->has('named', ['content', $findstore1]);
//    if ($findstore == NULL) {
//      throw new \Exception('Find your nearest store section is missing');
//    }
    $find_store = $page->hasLink('Locate a Store');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the footer');
    }
  }

  /**
   * @Then /^I should be able to see the header in Arabic$/
   */
  public function iShouldBeAbleToSeeTheHeaderInArabic()
  {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('تسجيل مستخدم جديد');
    if ($create_account == NULL) {
      throw new \Exception('Link for email sign up is missing in the header');
    }

    $find_store = $page->hasLink('البحث عن محل');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('English');
    if ($language == NULL) {
      throw new \Exception('Link to switch to English language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer in Arabic$/
   */
  public function iShouldBeAbleToSeeTheFooterInArabic()
  {
    $page = $this->getSession()->getPage();
    $termsandconditions = $page->find('css', '.c-footer-primary__region');
    $termsandconditions->hasLink('Privacy' and 'Terms & Conditions');
    if ($termsandconditions == NULL) {
      throw new \Exception('PRIVACY and TERMS & CONDITIONS links are missing in the footer');
    }

    $copyright1 = ' شركة ويليامز سونوما، جميع الحقوق محفوظة 2020 ©';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);
    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $connect1 = 'تواصل معنا';
    $connect = $page->find('css', '.c-footer-primary__region');
    $connect->has('named', ['content', $connect1]);
    if ($connect == NULL) {
      throw new \Exception('Connect with us section is missing');
    }
    $facebook = $page->hasLink('Facebook');
    if ($facebook == NULL) {
      throw new \Exception('Link to facebook is missing in the footer');
    }
    $instagram = $page->hasLink('Instagram');
    if ($instagram == NULL) {
      throw new \Exception('Link to instagram is missing in the footer');
    }

//    $findstore1 = 'اختر محلاً للتعرف على التفاصيل';
//    $findstore = $page->find('css', '.region-footer-primary');
//    $findstore->has('named', ['content', $findstore1]);
//    if ($findstore == NULL) {
//      throw new \Exception('Find your nearest store section is missing');
//    }
    $find_store = $page->hasLink('البحث عن محلاتنا');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the footer');
    }
  }

}