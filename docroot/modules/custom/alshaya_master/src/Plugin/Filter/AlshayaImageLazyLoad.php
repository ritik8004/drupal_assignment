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

    $picture_list = $dom->getElementsByTagName('picture');
    if ($picture_list->length > 0) {
      /** @var \DOMElement $picture */
      foreach ($picture_list as $picture) {
        foreach ($picture->getElementsByTagName('source') as $source) {
          $this->applyBlazyToSource($source);
        }

        foreach ($picture->getElementsByTagName('img') ?? [] as $image) {
          $this->applyBlazyToImage($image);
        }
      }
    }

    $image_list = $dom->getElementsByTagName('img');
    if ($image_list->length > 0) {
      /** @var \DOMElement $image */
      foreach ($image_list as $image) {
        $this->applyBlazyToImage($image);
      }
    }

    if ($this->modified) {
      $text = Html::serialize($dom);
      $text = Html::decodeEntities($text);
    }

    return new FilterProcessResult($text);
  }

  /**
   * Wrapper function to apply blazy to img tag.
   *
   * @param \DOMElement $image
   *   Image dom element.
   */
  private function applyBlazyToImage(\DOMElement $image) {
    if (strpos($image->getAttribute('class') ?? '', 'b-lazy') > -1) {
      return;
    }

    $this->modified = TRUE;

    $image->setAttribute('data-src', $image->getAttribute('src'));
    $image->setAttribute('src', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');

    $classes = [
      $image->getAttribute('class'),
      'b-lazy',
    ];

    $image->setAttribute('class', implode(' ', array_filter($classes)));
  }

  /**
   * Wrapper function to apply blazy to source tag.
   *
   * @param \DOMElement $source
   *   Source dom element.
   */
  private function applyBlazyToSource(\DOMElement $source) {
    if (strpos($source->getAttribute('class') ?? '', 'b-lazy') > -1) {
      return;
    }

    $this->modified = TRUE;

    $source->setAttribute('data-srcset', $source->getAttribute('srcset'));
    $source->setAttribute('srcset', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');

    $classes = [
      $source->getAttribute('class'),
      'b-lazy',
    ];

    $source->setAttribute('class', implode(' ', array_filter($classes)));
  }

}
