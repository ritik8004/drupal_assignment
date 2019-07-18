<?php

namespace Drupal\alshaya_performance\Plugin\Filter;

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
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $dom = Html::load($text);
    $image_list = $dom->getElementsByTagName('img');
    $block_body = $text;
    if ($image_list->length > 0) {
      foreach ($image_list as $image) {
        $src = !empty($image->getAttribute('data-src')) ? $image->getAttribute('data-src') : $image->getAttribute('src');
        $image->setAttribute('data-src', $src);
        $image->setAttribute('src', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
        $image->setAttribute('class', 'b-lazy');
        $dom->saveHTML($image);
      }
      $block_body = Html::serialize($dom);
      $block_body = Html::decodeEntities($block_body);
    }
    return new FilterProcessResult($block_body);
  }

}
