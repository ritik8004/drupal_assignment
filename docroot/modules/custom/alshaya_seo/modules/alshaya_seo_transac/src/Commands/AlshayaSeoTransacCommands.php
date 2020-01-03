<?php

namespace Drupal\alshaya_seo_transac\Commands;

use Drupal\alshaya_seo_transac\AlshayaSitemapManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\simple_sitemap\Simplesitemap;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaSeoTransacCommands.
 *
 * @package Drupal\alshaya_seo_transac\Commands
 */
class AlshayaSeoTransacCommands extends DrushCommands {

  /**
   * Alshaya Site Map manager.
   *
   * @var \Drupal\alshaya_seo_transac\AlshayaSitemapManager
   */
  private $sitemapManager;

  /**
   * Simple sitemap generator.
   *
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * AlshayaSeoTransacCommands constructor.
   *
   * @param \Drupal\alshaya_seo_transac\AlshayaSitemapManager $sitemap_manager
   *   Alshaya Site Map manager.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   *   Simple sitemap generator.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger channel factory.
   */
  public function __construct(AlshayaSitemapManager $sitemap_manager,
                              Simplesitemap $generator,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->sitemapManager = $sitemap_manager;
    $this->generator = $generator;
    $this->setLogger($loggerChannelFactory->get('AlshayaSeoTransacCommands'));
  }

  /**
   * Regenerate the XML sitemaps according to the module settings.
   *
   * @hook replace-command simple-sitemap:generate
   */
  public function simpleSitemapGenerateReplaceCommand() {
    // Configure the variants first.
    $this->sitemapManager->configureVariants();
    $this->generator->generateSitemap('drush');
  }

}
