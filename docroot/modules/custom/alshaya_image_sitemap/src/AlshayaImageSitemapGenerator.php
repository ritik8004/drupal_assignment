<?php

namespace Drupal\alshaya_image_sitemap;

use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\node\Entity\Node;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\node\NodeInterface;

/**
 * Class Alshaya Image Sitemap Generator.
 *
 * @package Drupal\alshaya_image_sitemap
 */
class AlshayaImageSitemapGenerator {

  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $database;

  /**
   * State service object.
   *
   * @var Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Simple Sitemap.
   *
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $simpleSitemap;

  /**
   * Current time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $currentTime;

  /**
   * AlshayaImageSitemapGenerator constructor.
   *
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $database
   *   Database service.
   * @param \Drupal\Core\State\StateInterface $state
   *   State interface service object.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $skuImagesManager
   *   SKU Images Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\simple_sitemap\Simplesitemap $simple_sitemap
   *   Simple sitemap.
   * @param \Drupal\Component\Datetime\TimeInterface $current_time
   *   Current time service.
   */
  public function __construct(Connection $database,
                              StateInterface $state,
                              FileSystemInterface $fileSystem,
                              EntityTypeManagerInterface $entity_manager,
                              ModuleHandlerInterface $module_handler,
                              TranslationInterface $string_translation,
                              LanguageManagerInterface $language_manager,
                              SkuImagesManager $skuImagesManager,
                              SkuManager $sku_manager,
                              ConfigFactory $configFactory,
                              Simplesitemap $simple_sitemap,
                              TimeInterface $current_time) {
    $this->database = $database;
    $this->state = $state;
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->stringTranslation = $string_translation;
    $this->languageManager = $language_manager;
    $this->skuImagesManager = $skuImagesManager;
    $this->skuManager = $sku_manager;
    $this->configFactory = $configFactory;
    $this->simpleSitemap = $simple_sitemap;
    $this->currentTime = $current_time;
  }

  /**
   * First function to run for sitemap generation.
   */
  public function getSitemapReady() {
    $output = '<?xml version="1.0" encoding="UTF-8"?>';
    $output .= '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="https://www.w3.org/1999/xhtml" xmlns:image="https://www.google.com/schemas/sitemap-image/1.1">';
    $schema = $this->configFactory->get('system.file')->get('default_scheme');
    $path = file_create_url($this->fileSystem->realpath($schema . "://alshaya_image_sitemap"));
    if (!is_dir($path)) {
      $this->fileSystem->mkdir($path);
    }
    $filename = 'image_sitemap.xml';
    $output = $this->formatXmlString($output);
    file_put_contents($path . '/' . $filename, print_r($output, TRUE));
    $this->state->set('alshaya_image_sitemap.url_count', 0);
  }

  /**
   * Get all published product nodes.
   */
  public function getNodes() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();

    return $query->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      // Add tag to ensure this can be altered easily in custom modules.
      ->addTag('get_display_node_for_sku')
      ->execute();
  }

  /**
   * Function to format images to display in sitemap.
   */
  public function process($nids) {
    $output = '';
    $total_urls = $this->state->get('alshaya_image_sitemap.url_count');
    $languages = $this->languageManager->getLanguages();
    $country_code = _alshaya_custom_get_site_level_country_code();
    $node_storage = $this->entityTypeManager->getStorage('node');
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');

    if ((is_countable($nids) ? count($nids) : 0) > 0) {
      foreach ($nids as $nid) {
        // Fetch list of media files for each nid.
        // Load product from id.
        $product = $node_storage->load($nid);

        $sitemap_settings = $this->simpleSitemap->getEntityInstanceSettings($product->getEntityTypeId(), $product->id());

        // Skip all nodes that are marked as not indexable in simple sitemap.
        if (!empty($sitemap_settings) && empty($sitemap_settings['index'])) {
          continue;
        }

        $media = [];
        $sku_for_gallery = NULL;

        if ($product instanceof Node) {
          // Get SKU from product.
          $skuId = $this->skuManager->getSkuForNode($product);
          if (!empty($skuId)) {
            $sku = SKU::loadFromSku($skuId);
            if ($sku instanceof SKU) {
              $sku_for_gallery = $sku;
              $combinations = $this->skuManager->getConfigurableCombinations($sku);
              // This code need to be in sync with PDP. The images that
              // are displayed on PDP page should be fetched here.
              if ($sku->bundle() == 'configurable') {
                // Add images from parent only on page load if images from child
                // are to be shown after selection of all children and there are
                // more than one configuration for this product.
                if ($display_settings->get('show_child_images_after_selecting') !== 'all'
                  || (is_countable($combinations['attribute_sku']) ? count($combinations['attribute_sku']) : 0) === 1) {

                  // Try to load images first for child to be displayed.
                  try {
                    $sku_for_gallery = $this->skuImagesManager->getSkuForGallery($sku, TRUE, 'fallback');
                  }
                  catch (\Exception) {
                    // Do nothing.
                  }
                }
              }

              if ($sku_for_gallery instanceof SKU) {
                $media = $this->skuImagesManager->getMediaImages($sku_for_gallery);
              }
            }
          }
        }

        if (!empty($media)) {
          $output .= '<url><loc>' . Url::fromRoute('entity.node.canonical', ['node' => $nid], [
            'absolute' => TRUE,
            'https' => TRUE,
          ])->toString() . '</loc>';
          foreach ($languages as $language) {
            $output .= '<xhtml:link rel="alternate" href="' . Url::fromRoute('entity.node.canonical', ['node' => $nid], [
              'absolute' => TRUE,
              'https' => TRUE,
              'language' => $language,
            ])->toString() . '" hreflang="' . $language->getId() . '-' . strtolower($country_code) . '"/>';
          }
          foreach ($media as $value) {
            if ($value) {
              $path = str_replace('&', '%26', $value);
              $output .= '<image:image><image:loc>' . $path . '</image:loc></image:image>';
            }
            $total_urls++;
          }
          $output .= '</url>';
        }
      }
    }
    $this->state->set('alshaya_image_sitemap.url_count', $total_urls);
    $output = $this->formatXmlString($output);
    $schema = $this->configFactory->get('system.file')->get('default_scheme');
    $path = file_create_url($this->fileSystem->realpath($schema . "://alshaya_image_sitemap"));
    $filename = 'image_sitemap.xml';
    file_put_contents($path . '/' . $filename, $output, FILE_APPEND);
  }

  /**
   * Function to run once generation is complete.
   */
  public function sitemapGenerateFinished() {
    $output = '</urlset>';
    $schema = $this->configFactory->get('system.file')->get('default_scheme');
    $path = file_create_url($this->fileSystem->realpath($schema . "://alshaya_image_sitemap"));
    $filename = 'image_sitemap.xml';
    $output = $this->formatXmlString($output);
    file_put_contents($path . '/' . $filename, $output, FILE_APPEND);
    $request_time = $this->currentTime->getRequestTime();
    $this->state->set('alshaya_image_sitemap.last_generated', $request_time);
  }

  /**
   * Function to format xml.
   */
  public function formatXmlString($xml) {
    $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
    $token = strtok($xml, "\n");
    $result = '';
    $pad = 0;
    $matches = [];
    while ($token !== FALSE) {
      if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
        $indent = 0;
      }
      elseif (preg_match('/^<\/\w/', $token, $matches)) {
        $pad--;
        $indent = 0;
      }
      elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
        $indent = 1;
      }
      else {
        $indent = 0;
      }
      $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
      $result .= $line . "\n";
      $token = strtok("\n");
      $pad += $indent;
    }
    return $result;
  }

}
