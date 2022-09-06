<?php

namespace Drupal\alshaya_search\Plugin\Field\FieldFormatter;

use Drupal\responsive_image\Plugin\Field\FieldFormatter\ResponsiveImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Cache\Cache;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'alshaya_search_gallery' formatter.
 *
 * @todo Need to check if this is used anywhere, if yes - need to update it
 * to include alt/title tags.
 *
 * @FieldFormatter(
 *   id = "alshaya_search_gallery",
 *   label = @Translation("Alshaya Search Gallery"),
 *   field_types = {
 *     "image"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class AlshayaSearchGalleryFormatter extends ResponsiveImageFormatter {

  /**
   * Thumbnail image style constant.
   */
  public const THUMB_IMG_STYLE = '59x60';

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $thumbnails = [];
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    // Collect cache tags to be added for each item in the field.
    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }

    $image_styles = $this->imageStyleStorage->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }

    foreach ($files as $file) {
      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item = $file->_referringItem;
      unset($item->_attributes);

      $thumbImg = ImageStyle::load(self::THUMB_IMG_STYLE)->buildUrl($file->getFileUri());
      $mainImg = ImageStyle::load($this->getSetting('responsive_image_style'))->buildUrl($file->getFileUri());
      $thumbnails[] = [
        'thumburl' => $thumbImg,
        'url' => $mainImg,
      ];

    }
    $elements = [
      '#theme' => 'alshaya_search_gallery',
      '#mainImage' => $thumbnails[0]['url'],
      '#thumbnails' => $thumbnails,
      '#cache' => [
        'tags' => $cache_tags,
      ],
      '#attached' => [
        'library' => [
          'alshaya_search/alshaya_search',
        ],
      ],
    ];

    return $elements;
  }

}
