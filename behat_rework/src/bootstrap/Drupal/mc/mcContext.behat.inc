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
class mcContext extends DrupalSubContextBase implements DrupalSubContextInterface {

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
    $create_account = $page->hasLink('create an account');
    if ($create_account == NULL) {
      throw new \Exception('Link for creating account is missing in the header');
    }

    $sign_in = $page->hasLink('Sign in');
    if ($sign_in == NULL) {
      throw new \Exception('Link to Sign in is missing in the header');
    }

    $find_store = $page->hasLink('Find Store');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header');
    }

    $language = $page->hasLink('عربية');
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
    $footer_region_categories = $page->find('css', '.footer--menu .footer--categories');
    $footer_region_categories->hasLink('Baby Clothing' and 'Toddler Clothing' and 'Maternity' and 'Bathing & Care' and 'Car Seats');
    $footer_region_categories = $footer_region_categories and $footer_region_categories->hasLink('Feeding' and 'Nursery & Bedroom' and 'Pushchairs');

    if ($footer_region_categories == NULL) {
      throw new \Exception ('Main menu is not being displayed in the footer');
    }

    $about_brand = $page->find('css', '.footer--menu .footer--abouthelp');
    $about_brand->hasLink('Corporate information' and 'Delivery information' and 'Exchange & refund' and 'Terms and Conditions');
    if ($about_brand == NULL) {
      throw new \Exception('About brand section missing in the footer');
    }

    $help = $page->find('css', '.footer--abouthelp');
    $help->hasLink('contact' and 'faq' and 'sitemap' and 'store');
    if ($help == NULL) {
      throw new \Exception('Help section missing in the footer');
    }

    $text = 'connect with us' and 'get email offers and the latest news from Mothercare Kuwait';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', ['content', $text]);
    if ($subscription == NULL) {
      throw new \Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('sign up');
    if ($sub_button == NULL) {
      throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = '© Copyright Mothercare UK Limited 2016 Registered in England no. 533087, VAT Reg no 440 6445 66';
    $copyright1 = $copyright1 and 'Registered ofﬁce: Cherry Tree Road, Watford, Hertfordshire, WD24 6SH';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);

    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $payment = $page->find('css', '.c-footer-secondary');
    $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
    if ($payment == NULL) {
      throw new \Exception('Payment links are missing in the footer');
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
      throw new \Exception('Link for creating account is missing in the header on Arabic site');
    }

    $sign_in = $page->hasLink('تسجيل الدخول');
    if ($sign_in == NULL) {
      throw new \Exception('Link to Sign in is missing in the header on Arabic site ');
    }

    $find_store = $page->hasLink('البحث عن محلاتنا');
    if ($find_store == NULL) {
      throw new \Exception('Link to find a store is missing in the header on Arabic site');
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
    $footer_region_categories = $page->find('css', '.footer--menu .footer--categories');
    $footer_region_categories->hasLink('ملابس الرضع' and 'الإستبدال و الر' and 'اتصل بنا');

    if ($footer_region_categories == NULL) {
      throw new \Exception ('Main menu is not being displayed in the footer');
    }

    $text = 'تواصل معنا' and 'مذركير الكويت احصل على أحدث العروض الحصرية عبر عنوان البريد الإكتروني';
    $subscription = $page->find('css', '.alshaya-newsletter-subscribe');
    $subscription->find('named_partial', ['content', $text]);
    if ($subscription == NULL) {
      throw new \Exception('Text related to Subscription is missing in the footer');
    }

    $sub_button = $subscription->hasButton('سجل الآن');
    if ($sub_button == NULL) {
      throw new \Exception('Sign up button to subscribe to newsletters is missing in the footer');
    }

    $copyright1 = '© حقوق النشر محفوظة لشركة مذركير المحدودة المملكة المتحدة 2015 | مذركير المحدودة المملكة المتحدة (شركة خاصة محدودة)';
    $copyright1 = $copyright1 and 'مسجلة في إنجلترا برقم 533087 . رقم تسجيل ضريبة القيمة المضافة 66 6445 440 ';
    $copyright1 = $copyright1 and 'مكتب التسجيل: شيري تري رود، واتفورد، هيرتفوردشاير، WD24 6SH';
    $copyright = $page->find('css', '.region__footer-secondary');
    $copyright->has('named', ['content', $copyright1]);

    if ($copyright == NULL) {
      throw new \Exception('Copyright information is missing in the footer');
    }

    $payment = $page->find('css', '.c-footer-secondary');
    $payment->hasLink('Mastercard' and 'Verision' and 'Visa');
    if ($payment == NULL) {
      throw new \Exception('Payment links are missing in the footer');
    }
  }
}
