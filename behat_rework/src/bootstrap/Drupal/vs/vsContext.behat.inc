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
class vsContext extends DrupalSubContextBase implements DrupalSubContextInterface {

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
      throw new \Exception('Link for create a account is missing in the header');
    }

    $sign_in = $page->hasLink('Sign In');
    if ($sign_in == NULL) {
      throw new \Exception('Link for email sign up is missing in the header');
    }

    $find_store = $page->hasLink('Find a Store');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('العربية');
    if ($language == NULL) {
      throw new \Exception('Link to switch to Arabic language is missing in the header');
    }
  }

  /**
   * @Given /^I should be able to see the footer$/
   */
  public function iShouldBeAbleToSeeTheFooter()
  {
    $page = $this->getSession()->getPage();
//    $termsandconditions = $page->find('css', '.region__footer-secondary');
//    $termsandconditions->hasLink('Privacy' and 'Terms & Conditions');
//    if ($termsandconditions == NULL) {
//      throw new \Exception('PRIVACY and TERMS & CONDITIONS links are missing in the footer');
//    }

    $copyright1 = '© Victoria\'s Secret. All Rights Reserved';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);
    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $connect1 = 'CONNECT WITH US';
    $connect = $page->find('css', '.c-footer-primary');
    $connect->has('named', ['content', $connect1]);
    if ($connect == NULL) {
      throw new \Exception('Connect with us section is missing');
    }
//    $facebook = $page->hasLink('Facebook');
//    if ($facebook == NULL) {
//      throw new \Exception('Link to facebook is missing in the footer');
//    }
//    $instagram = $page->hasLink('Instagram');
//    if ($instagram == NULL) {
//      throw new \Exception('Link to instagram is missing in the footer');
//    }
//
//    $findstore1 = 'FIND YOUR NEAREST STORE';
//    $findstore = $page->find('css', '.region-footer-primary');
//    $findstore->has('named', ['content', $findstore1]);
//    if ($findstore == NULL) {
//      throw new \Exception('Find your nearest store section is missing');
//    }
//    $find_store = $page->hasLink('Find Store');
//    if ($find_store == NULL) {
//      throw new \Exception('Link to find a store is missing in the footer');
//    }
  }

  /**
   * @Then /^I should be able to see the header in Arabic$/
   */
  public function iShouldBeAbleToSeeTheHeaderInArabic()
  {
    $page = $this->getSession()->getPage();
    $create_account = $page->hasLink('تسجيل مستخدم جديد');
    if ($create_account == NULL) {
      throw new \Exception('Link for create a account is missing in the header');
    }

    $sign_in = $page->hasLink('تسجيل الدخول');
    if ($sign_in == NULL) {
      throw new \Exception('Link for email sign up is missing in the header');
    }

    $find_store = $page->hasLink('البحث عن محلاتنا');
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
//    $termsandconditions = $page->find('css', '.region-footer-secondary');
//    $termsandconditions->hasLink('Privacy' and 'Terms & Conditions');
//    if ($termsandconditions == NULL) {
//      throw new \Exception('PRIVACY and TERMS & CONDITIONS links are missing in the footer');
//    }

    $copyright1 = 'ﭬيكتوريا سيكريت. كل الحقوق محفوظة. ©';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);
    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $connect1 = 'تواصلي معنا';
    $connect = $page->find('css', '.c-footer-primary');
    $connect->has('named', ['content', $connect1]);
    if ($connect == NULL) {
      throw new \Exception('Connect with us section is missing');
    }
//    $facebook = $page->hasLink('Facebook');
//    if ($facebook == NULL) {
//      throw new \Exception('Link to facebook is missing in the footer');
//    }
//    $instagram = $page->hasLink('Instagram');
//    if ($instagram == NULL) {
//      throw new \Exception('Link to instagram is missing in the footer');
//    }
//
//    $findstore1 = 'اختر محلاً للتعرف على التفاصيل';
//    $findstore = $page->find('css', '.region-footer-primary');
//    $findstore->has('named', ['content', $findstore1]);
//    if ($findstore == NULL) {
//      throw new \Exception('Find your nearest store section is missing');
//    }
//    $find_store = $page->hasLink('البحث عن محلاتنا');
//    if ($find_store == NULL) {
//      throw new \Exception('Link to find a store is missing in the footer');
//    }
  }

  /**
   * @When I remove promo panel
   */
  public function iRemovePromoPanel()
  {
    $page = $this->getSession()->getPage();
    $promopanel= $page->has('css','.promo-panel-fixed');
    if (null === $promopanel) {
      throw new \Exception("The element is not found");
    }
    else
    {
      $this->getSession()->executeScript("jQuery('.promo-panel-fixed').remove();");
    }
  }

  /**
   * @When I remove promo panel from delivery page
   */
  public function iRemovePromoPanelFromDeliveryPage()
  {
    $page = $this->getSession()->getPage();
    $promopanel= $page->has('css','.promo-panel-label');
    if (null === $promopanel) {
      throw new \Exception("The element is not found");
    }
    else
    {
      $this->getSession()->executeScript("jQuery('.promo-panel-label').remove();");
    }
  }

  /**
   * @Given /^I select "([^"]*)" attribute for the product$/
   */
  public function iSelectAttributeForTheProduct($attribute)
  {
    $session = $this->getSession();
    $page = $session->getPage();
    $attribute_wrapper = $page->findById('configurable_ajax');
    if ($attribute_wrapper) {
      $element = $attribute_wrapper->find('css', "select[name='configurables[$attribute]']")->getParent();
      if ($element) {
        $links = $element->findAll('css', '.select2Option li');
        if($links)
        {
          foreach($links as $link){
            if ($link->isVisible()) {
              $link->find('css', 'a')->click();
              break;
            }
          }
        }
      }
    }
  }
}
