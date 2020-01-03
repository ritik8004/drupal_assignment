<?php

namespace Drupal\alshaya_seo_transac\Plugin\simple_sitemap\SitemapType;

/**
 * Class AlshayaHreflangSitemapType.
 *
 * @SitemapType(
 *   id = "alshaya_hreflang",
 *   label = @Translation("Default hreflang"),
 *   description = @Translation("The default hreflang sitemap type."),
 *   sitemapGenerator = "default",
 *   urlGenerators = {
 *     "custom",
 *     "entity_menu_link_content",
 *     "arbitrary",
 *     "alshaya_entity",
 *   },
 * )
 */
class AlshayaHreflangSitemapType extends SitemapTypeBase {
}
