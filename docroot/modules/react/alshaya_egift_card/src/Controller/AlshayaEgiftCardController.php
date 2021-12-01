<?php

namespace Drupal\alshaya_egift_card\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AlshayaEgiftCardController extends ControllerBase {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container): AlshayaEgiftCardController {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * Egift card purchase page.
   *
   * @return array
   *   Markup for eGift card purchase react app.
   */
  public function eGiftCardPurchase():array {
    $config = $this->configFactory->get('alshaya_egift_card.settings');

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="egift-card-wrapper">loading...</div>',
      '#attached' => [
        'library' => [
          'alshaya_egift_card/alshaya_egift_card_purchase',
        ],
      ],
    ];

    $build['#cache']['tags'] = Cache::mergeTags([], $config->getCacheTags());

    $api_settings = Settings::get('alshaya_api.settings');

    // We proxy the requests via cloudflare, so we use the current domain as is
    // without any language suffix so HTTP_HOST is enough.
    $build['#attached']['drupalSettings']['egiftCard']['mdcMediaUrl'] = 'https://' . $_SERVER['HTTP_HOST'];

    // Use proxy on local env as here we don't have Cloudflare.
    if (Settings::get('env') === 'local') {
      $build['#attached']['drupalSettings']['egiftCard']['mdcMediaUrl'] = '/proxy/?url=' . $api_settings['magento_host'];
    }

    // @todo append mdc media path from config.
    $build['#attached']['drupalSettings']['egiftCard']['mdcMediaUrl'] .= '/media/catalog/product/';

    return $build;
  }

}
