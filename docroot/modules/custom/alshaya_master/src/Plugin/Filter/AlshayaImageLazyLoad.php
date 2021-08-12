<?php

namespace Drupal\alshaya_master\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Filter to find and process found taxonomy terms in the fields value.
 *
 * @Filter(
 *   id = "alshaya_image_lazy_load",
 *   title = @Translation("Alshaya Lazy Load Embedded Images"),
 *   description = @Translation("This filter is used to lazy load the images embedded in editor fields."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class AlshayaImageLazyLoad extends FilterBase {

  /**
   * Flag to say if content was modified or not.
   *
   * @var bool
   */
  private $modified;

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $this->modified = FALSE;

    $dom = Html::load($text);

    // Apply loading lazy to all the source and img tag for picture tag.
    $picture_list = $dom->getElementsByTagName('picture');
    if ($picture_list->length > 0) {
      /** @var \DOMElement $picture */
      foreach ($picture_list as $picture) {
        foreach ($picture->getElementsByTagName('source') as $source) {
          $this->applylazy($source);
        }

        foreach ($picture->getElementsByTagName('img') ?? [] as $image) {
          $this->applylazy($image);
        }
      }
    }

    // Apply loading lazy to individual img tags.
    // We won't process again if already processed once.
    $image_list = $dom->getElementsByTagName('img');
    if ($image_list->length > 0) {
      /** @var \DOMElement $image */
      foreach ($image_list as $image) {
        $this->applylazy($image);
      }
    }
    if ($this->modified) {
      $text = Html::serialize($dom);
      $text = Html::decodeEntities($text);
    }

    return new FilterProcessResult($text);
  }

  /**
   * Wrapper function to apply lazy to element.
   *
   * @param \DOMElement $element
   *   DOM element.
   */
  private function applylazy(\DOMElement $element) {
    if (strpos($element->getAttribute('loading') ?? '', 'lazy') > -1) {
      return;
    }

    // If not AMP page, no need to process.
    if (\Drupal::moduleHandler()->moduleExists('amp')) {
      if (\Drupal::service('router.amp_context')->isAmpRoute()) {
        return;
      }
    }

    $this->modified = TRUE;
    $element->setAttribute('loading', 'lazy');
  }

}
