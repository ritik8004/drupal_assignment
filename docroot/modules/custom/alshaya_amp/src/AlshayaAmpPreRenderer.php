<?php

namespace Drupal\alshaya_amp;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides a trusted callback to render amp body.
 */
class AlshayaAmpPreRenderer implements RenderCallbackInterface {

  /**
   * Adds a custom pre-render to 'amp_processed_text' element.
   *
   * @param array $element
   *   The element build array.
   *
   * @return array
   *   The altered block build array.
   */
  public static function ampBodyPreRender(array $element): array {
    // If not amp page, no need to process.
    if (!\Drupal::service('router.amp_context')->isAmpRoute()) {
      return $element;
    }

    if (isset($element['#markup'])) {
      $markup = str_ireplace(
        ['<img', '<video', '/video>', '<audio', '/audio>'],
        ['<amp-img', '<amp-video', '/amp-video>', '<amp-audio', '/amp-audio>'],
        $element['#markup']
      );

      // Adding height, width and layout parameter as required by the AMP.
      $amp_theme = \Drupal::config('amp.theme')->get('amptheme');
      $amp_setting = \Drupal::config('alshaya_amp.settings');
      $height = $amp_setting->get($amp_theme . '.image_height_ratio') ?: $amp_setting->get('image_height_ratio');
      $width = $amp_setting->get($amp_theme . '.image_width_ratio') ?: $amp_setting->get('image_width_ratio');
      // Removes '/' from img tag.
      $markup = preg_replace('/<amp-img(.*?)\/>/', '<amp-img$1>', $markup);
      $markup = preg_replace('/<amp-img(.*?)>/', '<amp-img$1 height="' . $height . '" width="' . $width . '" layout="responsive"></amp-img>', $markup);
      /** @var \Drupal\filter\Render\FilteredMarkup $element['#markup'] */
      $element['#markup'] = $element['#markup']::create($markup);

      // Adding allowed tag otherwise amp tags will be stripped off.
      // @see \Drupal\Core\Render\Renderer::ensureMarkupIsSafe().
      $element['#allowed_tags'] = array_merge(Xss::getAdminTagList(), [
        'amp-img',
        'amp-video',
        'amp-audio',
        'amp-iframe',
      ]);
    }

    return $element;
  }

}
