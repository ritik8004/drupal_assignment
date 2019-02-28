<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\Unicode;

/**
 * Class ProductHelper.
 *
 * @package Drupal\alshaya_acm_product
 */
class ProductHelper {

  use StringTranslationTrait;

  /**
   * Max length to show in short description.
   *
   * @var string
   */
  protected $shortDescMaxLength;

  /**
   * Use text summary to generate short description.
   *
   * @var bool
   */
  protected $useTextSummary;

  /**
   * SkuManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $display_settings = $config_factory->get('alshaya_acm_product.display_settings');
    $this->shortDescMaxLength = $display_settings->get('short_desc_characters');
    $this->useTextSummary = $display_settings->get('short_desc_text_summary');
  }

  /**
   * Get Short description based from HTML.
   *
   * @param array $build
   *   Build array to modify.
   * @param string $html
   *   HTML Markup.
   */
  public function updateShortDescription(array &$build, $html) {
    $short_desc['label'] = [
      '#markup' => $this->t('Short Description'),
    ];

    $desc = $this->createShortDescription($html);
    // It is very unlikely but description might be too short to process.
    if (!isset($desc['read_more'])) {
      $build['read_more_style']['value'] = [
        '#markup' => 'display:none;',
      ];
    }
    $short_desc['value'] = [
      '#markup' => $desc['html'],
    ];

    $build['short_desc'] = $short_desc;
  }

  /**
   * Create short description from given html.
   */
  public function createShortDescription($html, $limit = NULL) {
    $limit = ($limit == NULL) ? $this->shortDescMaxLength : $limit;
    $desc_stripped = strip_tags($html);
    // It is very unlikely but description might be too short to process.
    if (Unicode::strlen($html) <= $limit || Unicode::strlen($desc_stripped) <= $limit) {
      $return = [
        'html' => $html,
      ];
    }
    else {
      $desc = ($this->useTextSummary)
        ? text_summary($html, NULL, $limit)
        : Unicode::truncate($desc_stripped, $limit, TRUE, FALSE);

      $return = [
        'html' => $desc . '...',
        'read_more' => TRUE,
      ];
    }
    return $return;
  }

  /**
   * Process short description for ellipsis.
   *
   * Process the short description array and add the ellipses in the last html
   * tag so that its not rendered in the second line.
   *
   * @param string $short_desc
   *   Short description.
   *
   * @return string
   *   Short description.
   */
  public function processShortDescEllipsis(string $short_desc) {
    // If normal string without any html tag.
    if (strip_tags($short_desc) == $short_desc) {
      return $short_desc;
    }

    // Remove the ellipses appended at last if there any.
    if (Unicode::substr($short_desc, -3) == '...') {
      $short_desc = Unicode::substr($short_desc, 0, -3);
    }

    // To suppress errors by the DomDocument.
    libxml_use_internal_errors(TRUE);
    $dom = new \DOMDocument();
    $dom->loadHTML(mb_convert_encoding($short_desc, 'HTML-ENTITIES', 'UTF-8'));
    $last_child = &$dom->lastChild;
    // Iterate recursively until we reach the last child element.
    while ($last_child) {
      if (!$last_child->lastChild) {
        // Append ellipsis on last child element content.
        $last_child->textContent .= '...';
      }
      $last_child = &$last_child->lastChild;
    }

    $short_desc = trim($dom->saveHTML());

    return $short_desc;
  }

}
