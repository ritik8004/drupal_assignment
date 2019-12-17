<?php

namespace Drupal\alshaya_custom;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Utilty Class.
 */
class Utility {

  /**
   * Theme Handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Utility constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme Handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->themeHandler = $theme_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get theme name by alshaya_theme_type theme key.
   *
   * Get the theme name for the key 'alshaya_theme_type' set in the themes
   * *.info.yml file. $skip_themes variable checks if we need to skip few themes
   * or not as at the point of installation, installed themed is also available.
   *
   * @param string $theme_type
   *   Theme type.
   * @param array $skip_themes
   *   Themes for which need to skip value.
   *
   * @return mixed|string
   *   Theme name.
   */
  public function getThemeByThemeType($theme_type = '', array $skip_themes = []) {
    if (!empty($theme_type)) {
      foreach ($this->themeHandler->listInfo() as $theme) {
        // If the key matches the expected type and the theme has not been
        // flagged to be ignored.
        if (!empty($theme->info['alshaya_theme_type'])
          && $theme->info['alshaya_theme_type'] == $theme_type
          && !in_array($theme->getName(), $skip_themes)
          && !empty($this->entityTypeManager->getStorage('block')->loadByProperties(['theme' => $theme->getName()]))
        ) {
          return $theme->getName();
        }
      }
    }

    return NULL;
  }

  /**
   * Validation rule for credit card number.
   *
   * Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org
   * This code has been released into the public domain, however please
   * give credit to the original author where possible.
   *
   * @param string $number
   *   A credit card number.
   *
   * @return bool
   *   TRUE is credit card number is valid.
   *
   * @see: http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
   *
   * @link: https://cgit.drupalcode.org/webform/tree/src/Element/WebformCreditCardNumber.php?id=4feb0fbbfd8024970d6dfe7a9aa519bfcbc6d776#n74
   */
  public function alshayaValidCreditCardNumber($number) {
    // If number is not 15 or 16 digits return FALSE.
    if (!preg_match('/^\d{15,16}$/', $number)) {
      return FALSE;
    }

    // Set the string length and parity.
    $number_length = strlen($number);
    $parity = $number_length % 2;

    // Loop through each digit and do the maths.
    $total = 0;
    for ($i = 0; $i < $number_length; $i++) {
      $digit = $number[$i];
      // Multiply alternate digits by two.
      if ($i % 2 == $parity) {
        $digit *= 2;
        // If the sum is two digits, add them together (in effect).
        if ($digit > 9) {
          $digit -= 9;
        }
      }
      // Total up the digits.
      $total += $digit;
    }

    // If the total mod 10 equals 0, the number is valid.
    return ($total % 10 == 0) ? TRUE : FALSE;
  }

  /**
   * Sort the two weights.
   *
   * @param int $a
   *   Weight 1.
   * @param int $b
   *   Weight 2.
   *
   * @return int
   *   Sort status.
   */
  public static function weightArraySort($a, $b) {
    if (isset($a['weight']) && isset($b['weight'])) {
      return $a['weight'] < $b['weight'] ? -1 : 1;
    }
    return 0;
  }

}
