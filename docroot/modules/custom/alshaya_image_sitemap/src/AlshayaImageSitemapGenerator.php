<?php

namespace Drupal\alshaya_image_sitemap;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class AlshayaImageSitemapGenerator.
 *
 * @package Drupal\alshaya_image_sitemap
 */
class AlshayaImageSitemapGenerator {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * State service object.
   *
   * @var Drupal\Core\State\StateInterface
   */
  protected $stateInt;

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

  use StringTranslationTrait;

  /**
   * The language interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaImageSitemapGenerator constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   Database service.
   * @param \Drupal\Core\State\StateInterface $stateInt
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
   */
  public function __construct(Connection $database,
                              StateInterface $stateInt,
                              FileSystemInterface $fileSystem,
                              EntityTypeManagerInterface $entity_manager,
                              ModuleHandlerInterface $module_handler,
                              TranslationInterface $string_translation,
                              LanguageManagerInterface $language_manager) {
    $this->database = $database;
    $this->stateInt = $stateInt;
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entity_manager;
    $this->moduleHandler = $module_handler;
    $this->stringTranslation = $string_translation;
    $this->languageManager = $language_manager;
  }

  /**
   * First function to run for sitemap generation.
   */
  public function getSitemapReady() {
    $output = "";
    $output .= '<?xml version="1.0" encoding="UTF-8"?>';
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
    $path = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://alshaya_image_sitemap"));
    if (!is_dir($path)) {
      $this->fileSystem->mkdir($path);
    }
    $filename = 'image_sitemap.xml';
    $output = $this->formatXmlString($output);
    file_put_contents($path . '/' . $filename, print_r($output, TRUE));
    $this->stateInt->set('alshaya_image_sitemap.url_count', 0);
  }

  /**
   * Get all published product nodes.
   */
  public function getNodes() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $nids = $query->condition('type', 'acq_product')
      ->condition('status', NODE_PUBLISHED)
      ->execute();
    return $nids;
  }

  /**
   * Function to format images to display in sitemap.
   */
  public function process($nids) {
    $output = '';
    $total_urls = $this->stateInt->get('alshaya_image_sitemap.url_count');
    $this->moduleHandler
      ->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    if (count($nids) > 0) {
      foreach ($nids as $nid) {
        // Fetch list of media files for each nid.
        $media = [];
        if ($this->moduleHandler->moduleExists('alshaya_hm_images')) {
          $this->moduleHandler->invokeAll('media_image_sitemap_alter', [&$media, &$nid]);
        }
        else {
          $media = alshaya_acm_product_get_product_media($nid);
        }

        if (!empty($media)) {
          $languages = $this->languageManager->getLanguages();
          $country_code = _alshaya_custom_get_site_level_country_code();
          $output .= '<url><loc>' . Url::fromRoute('entity.node.canonical', ['node' => $nid], ['absolute' => TRUE])->toString() . '</loc>';
          $output .= '<xhtml:link rel="alternate" href="' . Url::fromRoute('entity.node.canonical', ['node' => $nid], ['absolute' => TRUE, 'language' => $languages['en']])->toString() . '" hreflang="en-' . strtolower($country_code) . '"/>';
          $output .= '<xhtml:link rel="alternate" href="' . Url::fromRoute('entity.node.canonical', ['node' => $nid], ['absolute' => TRUE, 'language' => $languages['ar']])->toString() . '" hreflang="ar-' . strtolower($country_code) . '"/>';

          foreach ($media as $media_item) {
            if (isset($media_item['media_type']) && $media_item['media_type'] == 'image') {
              $output .= '<image:image><image:loc>' . file_create_url($media_item['file']->getFileUri()) . '</image:loc><image:title>' . $media_item['file']->getFilename() . '</image:title></image:image>';
            }

            elseif (isset($media_item['url'])) {
              $output .= '<image:image><image:loc>' . str_replace('&', '%26', $media_item['url']->toString()) . '</image:loc></image:image>';
            }
            $total_urls++;
          }
          $output .= '</url>';
        }
      }
    }
    $this->stateInt->set('alshaya_image_sitemap.url_count', $total_urls);
    $output = $this->formatXmlString($output);
    $path = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://alshaya_image_sitemap"));
    $filename = 'image_sitemap.xml';
    file_put_contents($path . '/' . $filename, $output, FILE_APPEND);
  }

  /**
   * Function to run once generation is complete.
   */
  public function sitemapGenerateFinished() {
    $output = '</urlset>';
    $path = file_create_url($this->fileSystem->realpath(file_default_scheme() . "://alshaya_image_sitemap"));
    $filename = 'image_sitemap.xml';
    $output = $this->formatXmlString($output);
    file_put_contents($path . '/' . $filename, $output, FILE_APPEND);
    $this->stateInt->set('alshaya_image_sitemap.last_generated', REQUEST_TIME);
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
