<?php

namespace Drupal\alshaya_image_styles\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class AlshayaImageStylesCommands.
 *
 * @package Drupal\alshaya_image_styles\Commands
 */
class AlshayaImageStylesCommands extends DrushCommands {

  /**
   * An array containing list of product image styles.
   *
   * @var string[]
   */
  public static $allProductImageStyles = [
    'media_library',
    'pdp_gallery_thumbnail',
    'product_zoom_large_800x800',
    'product_zoom_medium_606x504',
    'product_listing',
    'product_teaser',
    'magazine_article_listing',
    'magazine_article_hero',
    'magazine_article_home',
  ];

  /**
   * Image style storage object.
   *
   * @var \Drupal\image\Entity\ImageStyle
   */
  protected $imageStyle;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaImageStylesCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_channel_factory,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->logger = $logger_channel_factory->get('alshaya_image_Style');
    $this->imageStyle = $entity_type_manager->getStorage('image_style');
    $this->configFactory = $config_factory;
  }

  /**
   * Enable cloudflare for image styles.
   *
   * @param string $status
   *   The status of cloudflare for image style.
   * @param array|null[] $options
   *   Command options.
   *
   * @command alshaya_image_Styles:cf
   *
   * @aliases alshaya-image-styles-cf
   *
   * @usage drush alshaya-image-styles-cf enable
   *   Enable cloudflare for all image styles.
   * @usage drush alshaya-image-styles-cf disable
   *   Disable cloudflare for all image styles.
   * @usage drush alshaya-image-styles-cf enable --styles=product_listing,product_teaser
   *   Enable cloudflare for listed image styles.
   * @usage drush alshaya-image-styles-cf disable --styles=product_listing,product_teaser
   *   Disable cloudflare for listed image styles.
   *
   * @throws \Drush\Exceptions\UserAbortException
   */
  public function imageStylesEnableCloudFlare(string $status, array $options = ['styles' => NULL]) {
    $styles = $options['styles'];
    $image_styles = !empty($styles) ? explode(',', $styles) : [];
    if (empty($styles)) {
      $input = $this->io()->confirm(
        dt('Are you sure you want to @status cloudflare for all image styles?', [
          '@status' => $status,
        ])
      );

      if (!$input) {
        throw new UserAbortException();
      }

      $image_styles = self::$allProductImageStyles;
    }

    foreach ($image_styles as $style_id) {
      $this->changeStyleStatus($style_id, $status);
    }
  }

  /**
   * Change status for given style id.
   *
   * @param string $style_id
   *   The style id to be updated.
   * @param string $status
   *   The status: (enable / disable)
   */
  protected function changeStyleStatus(string $style_id, string $status) {
    $cf_status = ($status == 'enable');
    $cf_effect = [];
    if ($cf_status) {
      // Load all effects of given style id.
      $style = $this->imageStyle->load($style_id);
      $effects = $style->getEffects();
      $cf_effect[] = 'fit=contain';
      foreach ($effects as $effect) {
        // Get configuration of effects, which contains width, height,
        // scale, quality etc.
        $configuration = $effect->getConfiguration();
        if ($configuration['id'] === 'image_scale') {
          unset($configuration['data']['upscale']);
        }
        $cf_effect[] = $this->joinKeyValue($configuration['data']);
      }
    }
    // Update image style config.
    $styleConfig = $this->configFactory->getEditable('image.style.' . $style_id);
    $styleConfig->set('serve_from_cf', $cf_status);
    if (!empty($cf_effect) && $cf_status) {
      $styleConfig->set('cf_effect', implode(',', $cf_effect));
    }
    $styleConfig->save();

    $this->output->writeln(dt('Changing cloudflare status for @style_id to @status.', [
      '@style_id' => $style_id,
      '@status' => ($cf_status) ? 'enable with :: ' . implode(',', $cf_effect) : 'disable',
    ]));
  }

  /**
   * Helper method to prepare cloudflare string from array.
   *
   * @param array $input_array
   *   Array from which it will prepare a string.
   *
   * @return string
   *   String to be used as cf effect.
   */
  protected function joinKeyValue(array $input_array) {
    // Remove key with null values.
    $clear_input_array = array_filter($input_array);
    // Prepare string with comma separate values for multiple keys.
    return implode(',', array_map(
      function ($value, $key) {
        return sprintf("%s=%s", $key, $value);
      },
      $clear_input_array,
      array_keys($clear_input_array)
    ));
  }

}
