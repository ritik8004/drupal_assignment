<?php

namespace Drupal\alshaya_rcs_product\EventSubscriber;

use Drupal\rcs_placeholders\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AlshayaRcsPhProductPathProcessorEventSubscriber implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      RcsPhPathProcessorEvent::EVENT_NAME => [
        ['onPathProcess'],
      ],
    ];
  }

  public function onPathProcess(RcsPhPathProcessorEvent $event) {
    $data = $event->getData();
    $path = $data['path'];
    $full_path = $data['full_path'];
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $product_prefix = $config->get('product.path_prefix');

    if (str_starts_with($path, '/' . $product_prefix)) {
      $event->setData([
        'entityType'=> 'product',
        'entityPath' => substr_replace($path, '', 0, strlen($product_prefix) + 1),
        'entityPathPrefix' => $product_prefix,
        'entityFullPath' => $full_path,
      ]);
      return;
      // self::$entityType = 'product';
      // self::$entityPath = substr_replace($path, '', 0, strlen($product_prefix) + 1);
      // self::$entityPathPrefix = $product_prefix;
      // self::$entityFullPath = $full_path;

      // self::$processedPaths[$path] = '/node/' . $config->get('product.placeholder_nid');

      // $product = $config->get('product.enrichment') ? $this->getEnrichedEntity('product', $path) : NULL;
      // if (isset($product)) {
      //   self::$entityData = $product->toArray();
      //   self::$processedPaths[$path] = '/node/' . $product->id();
      // }

      // return self::$processedPaths[$path];
    }
  }
}
