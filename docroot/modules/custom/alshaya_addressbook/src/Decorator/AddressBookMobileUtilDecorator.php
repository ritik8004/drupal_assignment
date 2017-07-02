<?php

namespace Drupal\alshaya_addressbook\Decorator;

use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\mobile_number\MobileNumberUtil;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Utility\Token;

/**
 * Class AddressBookMobileUtilDecorator.
 */
class AddressBookMobileUtilDecorator extends MobileNumberUtil {

  /**
   * The mobile util service.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * AddressBookMobileUtilDecorator constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The original mobile_util service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   Flood object.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   Field manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   Country manager.
   * @param \Drupal\Core\Utility\Token $token
   *   Token manager.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util, ConfigFactoryInterface $config_factory, FloodInterface $flood, EntityFieldManagerInterface $field_manager, ModuleHandlerInterface $module_handler, CountryManagerInterface $country_manager, Token $token) {
    $this->mobileUtil = $mobile_util;
    parent::__construct($config_factory, $flood, $field_manager, $module_handler, $country_manager, $token);
  }

  /**
   * {@inheritdoc}
   */
  public function getMobileNumber($number, $country = NULL, $types = [1 => 1, 2 => 2]) {
    // Remove leading zero due to which number is un-recognizable.
    $number = ltrim($number, 0);
    return $this->mobileUtil->getMobileNumber($number, $country, $types);
  }

  /**
   * Get phone number as string.
   *
   * @param string $number
   *   Number.
   * @param null|string $country
   *   Country.
   * @param array $types
   *   Types to check.
   *
   * @return string
   *   Full phone number.
   */
  public function getPhoneNumberAsString($number, $country = NULL, array $types = [1 => 1, 2 => 2]) {
    $phone = $this->getMobileNumber($number, $country, $types);
    return $this->libUtil()->format($phone, 1);
  }

  /**
   * Get only mobile number as string.
   *
   * @param string $number
   *   Number.
   * @param null|string $country
   *   Country.
   * @param array $types
   *   Types to check.
   *
   * @return string
   *   Mobile number.
   */
  public function getMobileNumberAsString($number, $country = NULL, array $types = [1 => 1, 2 => 2]) {
    $phone = $this->getMobileNumber($number, $country, $types);
    return $phone->getNationalNumber();
  }

}
