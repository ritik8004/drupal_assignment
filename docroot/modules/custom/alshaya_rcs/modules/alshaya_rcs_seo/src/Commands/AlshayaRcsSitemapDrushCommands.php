<?php

namespace Drupal\alshaya_rcs_seo\Commands;

use Drush\Commands\DrushCommands;

/**
 * Expose drush commands for alshaya_rcs_seo.
 */
class AlshayaRcsSiteMapDrushCommands extends DrushCommands {

  /**
   * Removes the old sitemap mapping and adds the new MDC sitemap mapping.
   *
   * @command alshaya_rcs_seo:use-new-sitemap
   *
   * @aliases use-new-sitemap
   *
   * @usage drush use-new-sitemap
   *   Use the new sitemap which includes sitemap from MDC.
   */
  public function useNewSiteMap() {
    // @todo Logic to use the new sitemap and remove the existing entries.
  }

  /**
   * Removes the new sitemap mapping and rolls back to original sitemap.
   *
   * @command alshaya_rcs_seo:use-old-sitemap
   *
   * @aliases use-old-sitemap
   *
   * @usage drush use-old-sitemap
   *   Use the old sitemap which includes Drupal products & categories.
   */
  public function useOldSiteMap() {
    // @todo Logic to roleback to the initial sitemap.
  }

}
