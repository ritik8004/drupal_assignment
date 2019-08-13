<?php

namespace Drupal\alshaya_acm_product\Plugin\search_api\processor;

use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Query\ResultSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Show only one price facet final_price or selling_price.
 *
 * @SearchApiProcessor(
 *   id = "price_from_to_facets_processor",
 *   label = @Translation("From-To price processor"),
 *   description = @Translation("Show only one price facet final_price or selling_price."),
 *   stages = {
 *     "postprocess_query" = 100,
 *   },
 *   locked = true
 * )
 */
class PriceFromToProcessor extends ProcessorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * SKU Price Helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  private $priceHelper;

  /**
   * CleanActiveFacetsProcessor constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $price_helper
   *   SKU Price Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuPriceHelper $price_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->priceHelper = $price_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product.price_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function postprocessSearchResults(ResultSetInterface $results) {
    $facets = $results->getExtraData('search_api_facets');

    // Show only one price facet - final_price or selling_price.
    if ($this->priceHelper->isPriceModeFromTo() && isset($facets['final_price'])) {
      unset($facets['final_price']);
    }
    elseif (isset($facets['attr_selling_price'])) {
      unset($facets['attr_selling_price']);
    }

    $results->setExtraData('search_api_facets', $facets);
  }

}
