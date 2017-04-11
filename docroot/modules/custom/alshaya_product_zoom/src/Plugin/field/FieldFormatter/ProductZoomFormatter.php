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
      'thumb_style' => 0,
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
      '#title' => $this->t('Zoom area image style'),
      '#empty_option' => $this->t('None (original image)'),
      '#type' => 'select',
      '#options' => $image_styles,
    ];

    $element['thumb_style'] = [
      '#default_value' => $this->getSetting('thumb_style'),
      '#title' => $this->t('Thumbnail image style'),
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

    $properties = self::getRelStringForProductZoom($settings);

    // Thumbnail Image style.
    $thumbnail_style = $settings['thumb_style'];

    // Zoom Image style.
    $zoom_style = $settings['zoom_style'];

    // Slide Style.
    $slide_style = $settings['slide_style'];

    $thumbnails = [];
    $main_image = [];
    foreach ($items as $delta => $item) {
      $image_field = $item->getValue();
      $file_uri = File::load($image_field['target_id'])->getFileUri();

      if ($delta == 0) {
        $imageZoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
        $imageMedium = ImageStyle::load($slide_style)->buildUrl($file_uri);
        $main_image = [
          'zoomurl' => $imageZoom,
          'mediumurl' => $imageMedium,
        ];
      }
      $imageSmall = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
      $imageZoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
      $imageMedium = ImageStyle::load($slide_style)->buildUrl($file_uri);
      $thumbnails[] = [
        'thumburl' => $imageSmall,
        'mediumurl' => $imageMedium,
        'zoomurl' => $imageZoom,
        'type' => 'image',
      ];
    }

    // Build Videos as part of our list.
    // @todo: Get real videos.

    $videos = [
      'https://www.youtube.com/embed/eKG08z85DtY',
      'https://player.vimeo.com/video/1084537',
    ];

    foreach ($videos as $video) {
      if (strpos($video, 'youtube')) {
        $thumbnails[] = [
          'thumburl' => 'https://img.youtube.com/vi/' . self::getYouTubeVideoId($video) . '/hqdefault.jpg',
          'url' => $video,
          'type' => 'youtube',
          'width' => 81,
          'height' => 81,
        ];
      }
      else {
        $thumbnails[] = [
          'url' => $video,
          'type' => 'vimeo',
          'width' => 81,
          'height' => 81,
        ];
      }
    }

    $element = [
      '#theme' => 'product_zoom_gallery',
      '#mainImage' => $main_image,
      '#thumbnails' => $thumbnails,
      '#properties' => $properties,
      '#attached' => [
        'library' => [
          'alshaya_product_zoom/product.cloud_zoom',
        ],
      ],
    ];

    return $element;
  }

  /**
   * Get the rel attribute for Alshaya Product zoom.
   *
   * @param array $settings
   *   Product Zoom settings.
   *
   * @return string
   *   return the rel attribute.
   */
  public static function getRelStringForProductZoom(array $settings) {

    $string = '';
    $string .= "zoomWidth:'" . $settings['zoom_width'] . "'";
    $string .= ",zoomHeight:'" . $settings['zoom_height'] . "'";
    $string .= ",position:'" . $settings['zoom_position'] . "'";
    $string .= ",adjustX:'" . $settings['adjust_x'] . "'";
    $string .= ",adjustY:'" . $settings['adjust_y'] . "'";
    $string .= ",tint:'" . $settings['tint'] . "'";
    $string .= ",tintOpacity:'" . $settings['tint_opacity'] . "'";
    $string .= ",lensOpacity:'" . $settings['lens_opacity'] . "'";
    $string .= ",softFocus:" . $settings['soft_focus'];
    $string .= ",smoothMove:'" . $settings['smooth_move'] . "'";

    return $string;
  }

  /**
   * Get the youtube video ID from URL.
   *
   * @param string $url
   *   Youtube URLs.
   *
   * @return string
   *   The youtube video id.
   */
  public static function getYouTubeVideoId($url) {
    $video_id = FALSE;
    $url = parse_url($url);
    if (strcasecmp($url['host'], 'youtu.be') === 0) {
      // (dontcare)://youtu.be/<video id>.
      $video_id = substr($url['path'], 1);
    }
    elseif (strcasecmp($url['host'], 'www.youtube.com') === 0) {
      if (isset($url['query'])) {
        parse_str($url['query'], $url['query']);
        if (isset($url['query']['v'])) {
          // (dontcare)://www.youtube.com/(dontcare)?v=<video id>.
          $video_id = $url['query']['v'];
        }
      }
      if ($video_id == FALSE) {
        $url['path'] = explode('/', substr($url['path'], 1));
        if (in_array($url['path'][0], ['e', 'embed', 'v'])) {
          // (dontcare)://www.youtube.com/(whitelist)/<video id>.
          $video_id = $url['path'][1];
        }
      }
    }
    return $video_id;
  }

}
