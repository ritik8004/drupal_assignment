<?php

namespace Drupal\acq_checkout;

use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\LocaleHelper;
use Drupal\address\FieldHelper;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides an address formatter.
 */
class ACQAddressFormatter {

  /**
   * {@inheritdoc}
   */
  public function render($address) {
    $element = [
      '#prefix' => '<p class="address" translate="no">',
      '#suffix' => '</p>',
      '#post_render' => [
        [$this::class, 'postRender'],
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
        ],
      ],
    ] + $this->viewElement($address, 'en');

    return render($element);
  }

  /**
   * Builds a renderable array for a single address item.
   *
   * @param object $address
   *   The address.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array.
   */
  protected function viewElement($address, $langcode) {
    $country_code = $address->country_id;
    $country_repository = \Drupal::service('address.country_repository');
    $countries = $country_repository->getList();
    $address_format_repository = \Drupal::service('address.address_format_repository');
    $address_format = $address_format_repository->get($country_code);
    $values = $this->getValues($address, $country_code, $address_format);

    $element = [];
    $element['address_format'] = [
      '#type' => 'value',
      '#value' => $address_format,
    ];
    $element['locale'] = [
      '#type' => 'value',
      '#value' => 'en',
    ];
    $element['country_code'] = [
      '#type' => 'value',
      '#value' => $country_code,
    ];
    $element['country'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => ['class' => ['country']],
      '#value' => Html::escape($countries[$country_code]),
      '#placeholder' => '%country',
    ];
    foreach ($address_format->getUsedFields() as $field) {
      $property = FieldHelper::getPropertyName($field);
      $class = str_replace('_', '-', $property);

      $element[$property] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => ['class' => [$class]],
        '#value' => $values[$field],
        '#placeholder' => '%' . $field,
      ];
    }

    return $element;
  }

  /**
   * Gets the address values used for rendering.
   *
   * @param object $address
   *   The address.
   * @param string $country_code
   *   The country code.
   * @param \CommerceGuys\Addressing\AddressFormat\AddressFormat $address_format
   *   The address format.
   *
   * @return array
   *   The values, keyed by address field.
   */
  protected function getValues($address, $country_code, AddressFormat $address_format) {
    $values = [];
    $values['givenName'] = $address->first_name ?? '';
    $values['additionalName'] = $address->additional_name ?? '';
    $values['familyName'] = $address->last_name ?? '';
    $values['organization'] = $address->organization ?? '';
    $values['addressLine1'] = $address->street ?? '';
    $values['addressLine2'] = $address->street2 ?? '';
    $values['postalCode'] = $address->postcode ?? '';
    $values['sortingCode'] = $address->sortcode ?? '';
    $values['administrativeArea'] = $address->state ?? '';
    $values['locality'] = $address->city ?? '';
    $values['dependentLocality'] = $address->dependent_locality ?? '';

    $original_values = [];
    $subdivision_fields = $address_format->getUsedSubdivisionFields();
    $parents = [];
    foreach ($subdivision_fields as $index => $field) {
      if (empty($values[$field])) {
        // This level is empty, so there can be no sublevels.
        break;
      }
      $parents[] = $index ? $original_values[$subdivision_fields[$index - 1]] : $address->country_id;
      $subdivision_repository = \Drupal::service('address.subdivision_repository');
      $subdivision = $subdivision_repository->get($values[$field], $parents);
      if (!$subdivision) {
        break;
      }

      // Remember the original value so that it can be used for $parents.
      $original_values[$field] = $values[$field];
      // Replace the value with the expected code.
      $use_local_name = LocaleHelper::match('en', $subdivision->getLocale());
      $values[$field] = $use_local_name ? $subdivision->getLocalCode() : $subdivision->getCode();
      if (!$subdivision->hasChildren()) {
        // The current subdivision has no children, stop.
        break;
      }
    }

    return $values;
  }

  /**
   * Inserts the rendered elements into the format string.
   *
   * @param string $content
   *   The rendered element.
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element.
   *
   * @return string
   *   The new rendered element.
   */
  public static function postRender($content, array $element) {
    /** @var \CommerceGuys\Addressing\AddressFormat\AddressFormat $address_format */
    $address_format = $element['address_format']['#value'];
    $locale = $element['locale']['#value'];
    // Add the country to the bottom or the top of the format string,
    // depending on whether the format is minor-to-major or major-to-minor.
    if (LocaleHelper::match($address_format->getLocale(), $locale)) {
      $format_string = '%country' . "\n" . $address_format->getLocalFormat();
    }
    else {
      $format_string = $address_format->getFormat() . "\n" . '%country';
    }

    $replacements = [];
    foreach (Element::getVisibleChildren($element) as $key) {
      $child = $element[$key];
      if (isset($child['#placeholder'])) {
        $replacements[$child['#placeholder']] = $child['#value'] ? $child['#markup'] : '';
      }
    }
    $content = self::replacePlaceholders($format_string, $replacements);
    $content = nl2br($content, FALSE);

    return $content;
  }

  /**
   * Replaces placeholders in the given string.
   *
   * @param string $string
   *   The string containing the placeholders.
   * @param array $replacements
   *   An array of replacements keyed by their placeholders.
   *
   * @return string
   *   The processed string.
   */
  public static function replacePlaceholders($string, array $replacements) {
    // Make sure the replacements don't have any unneeded newlines.
    $replacements = array_map('trim', $replacements);
    $string = strtr($string, $replacements);
    // Remove noise caused by empty placeholders.
    $lines = explode("\n", $string);
    foreach ($lines as $index => $line) {
      // Remove leading punctuation, excess whitespace.
      $line = trim(preg_replace('/^[-,]+/', '', $line, 1));
      $line = preg_replace('/\s\s+/', ' ', $line);
      $lines[$index] = $line;
    }
    // Remove empty lines.
    $lines = array_filter($lines);

    return implode("\n", $lines);
  }

}
