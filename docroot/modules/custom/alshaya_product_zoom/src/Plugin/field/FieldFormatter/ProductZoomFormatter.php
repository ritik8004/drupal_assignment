<?php

namespace Drupal\alshaya_product_zoom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'alshaya_product_zoom' formatter.
 *
 * @FieldFormatter(
 *   id = "alshaya_product_zoom",
 *   label = @Translation("Alshaya Product Zoom"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ProductZoomFormatter extends ImageFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'slide_style' => 0,
      'zoom_style' => 0,
      'zoom_width' => '0',
      'zoom_height' => '0',
      'zoom_position' => 'right',
      'adjust_x' => '0',
      'adjust_y' => '0',
      'tint' => 'false',
      'tint_opacity' => '0.5',
      'lens_opacity' => '0.5',
      'soft_focus' => 'true',
      'smooth_move' => '3',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $element = parent::settingsForm($form, $form_state);
    $element['slide_style'] = [
      '#default_value' => $this->getSetting('slide_style'),
      '#title' => $this->t('Slide image style'),
      '#empty_option' => $this->t('None (original image)'),
      '#type' => 'select',
      '#options' => $image_styles,
    ];

    $element['zoom_style'] = [
      '#default_value' => $this->getSetting('zoom_style'),
      '#title' => $this->t('Zoom image style'),
      '#empty_option' => $this->t('None (original image)'),
      '#type' => 'select',
      '#options' => $image_styles,
    ];

    $element['zoom_width'] = [
      '#default_value' => $this->getSetting('zoom_width'),
      '#title' => $this->t('Zoom width'),
      '#description' => $this->t("The width of the zoom window in pixels.</br> If 'auto' is specified, the width will be the same as the small image."),
      '#type' => 'textfield',
    ];

    $element['zoom_height'] = [
      '#default_value' => $this->getSetting('zoom_height'),
      '#title' => $this->t('Zoom height'),
      '#description' => $this->t("The height of the zoom window in pixels.</br> If 'auto' is specified, the height will be the same as the small image."),
      '#type' => 'textfield',
    ];

    $element['zoom_position'] = [
      '#default_value' => $this->getSetting('zoom_position'),
      '#title' => $this->t('Zoom Position'),
      '#description' => $this->t("Specifies the position of the zoom window relative to the small image.</br> Allowable values are 'left', 'right', 'top', 'bottom', 'inside',</br> or you can specifiy the id of an html element to place the zoom window in e.g. position: 'element1"),
      '#type' => 'textfield',
    ];

    $element['adjust_x'] = [
      '#default_value' => $this->getSetting('adjust_x'),
      '#title' => $this->t('Adjust X'),
      '#description' => $this->t('Allows you to fine tune the x-position of the zoom window in pixels.'),
      '#type' => 'textfield',
    ];

    $element['adjust_y'] = [
      '#default_value' => $this->getSetting('adjust_y'),
      '#title' => $this->t('Adjust Y'),
      '#description' => $this->t('Allows you to fine tune the y-position of the zoom window in pixels.'),
      '#type' => 'textfield',
    ];

    $element['tint'] = [
      '#default_value' => $this->getSetting('tint'),
      '#title' => $this->t('Tint'),
      '#description' => $this->t("Specifies a tint colour which will cover the small image.</br> Colours should be specified in hex format,</br> e.g. '#aa00aa'. Does not work with softFocus."),
      '#type' => 'textfield',
    ];

    $element['tint_opacity'] = [
      '#default_value' => $this->getSetting('tint_opacity'),
      '#title' => $this->t('Tint'),
      '#description' => $this->t('Opacity of the tint, where 0 is fully transparent, and 1 is fully opaque.'),
      '#type' => 'textfield',
    ];

    $element['lens_opacity'] = [
      '#default_value' => $this->getSetting('lens_opacity'),
      '#title' => $this->t('Lens opacity'),
      '#description' => $this->t('Opacity of the lens mouse pointer, where 0 is fully transparent,</br> and 1 is fully opaque. In tint and soft-focus modes, it will always be transparent.'),
      '#type' => 'textfield',
    ];

    $element['soft_focus'] = [
      '#default_value' => $this->getSetting('soft_focus'),
      '#title' => $this->t('Soft Focus'),
      '#description' => $this->t('Applies a subtle blur effect to the small image.</br> Set to true or false. Does not work with tint.'),
      '#type' => 'textfield',
    ];

    $element['smooth_move'] = [
      '#default_value' => $this->getSetting('smooth_move'),
      '#title' => $this->t('Smooth Move'),
      '#description' => $this->t('Amount of smoothness/drift of the zoom image as it moves.</br> The higher the number, the smoother/more drifty the movement will be. 1 = no smoothing.'),
      '#type' => 'textfield',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = t('Settings:');

    foreach ($settings as $key => $value) {
      $summary[] = $key . ':' . $value;
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $settings = $this->getSettings();

    // Thumbnail Image style.
    $thumbnail_style = $settings['slide_style'];

    // Zoom Image style.
    $zoom_style = $settings['zoom_style'];

    $thumbnails = [];
    $main_image = [];

    foreach ($items as $delta => $item) {

      $image_field = $item->getValue();
      $file_uri = File::load($image_field['target_id'])->getFileUri();

      if ($delta == 0) {
        $imageLarge = ImageStyle::load('300x300')->buildUrl($file_uri);
        $imageZoom = ImageStyle::load('300x300')->buildUrl($file_uri);
        $main_image = [
          'url' => $imageZoom,
          'image' => $imageLarge,
        ];
      }

      $imageSmall = ImageStyle::load('144x144')->buildUrl($file_uri);
      $imageZoom = ImageStyle::load('300x300')->buildUrl($file_uri);
      $thumbnails[] = [
        'url' => $imageZoom,
        'image' => $imageSmall,
      ];

    }

    $element = [
      '#theme' => 'product_zoom_gallery',
      '#mainImage' => $main_image,
      '#thumbnails' => $thumbnails,
      '#attached' => [
        'library' => [
          'alshaya_product_zoom/product.cloud_zoom',
        ],
      ],
    ];

    return $element;
  }

}
