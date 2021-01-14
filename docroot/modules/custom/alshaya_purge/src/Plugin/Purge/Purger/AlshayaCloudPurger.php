<?php

namespace Drupal\alshaya_purge\Plugin\Purge\Purger;

use Drupal\acquia_purge\Plugin\Purge\Purger\AcquiaCloudPurger;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\purge\Plugin\Purge\Invalidation\InvalidationInterface;
use Drupal\acquia_purge\HostingInfoInterface;
use Drupal\acquia_purge\Hash;

/**
 * Alshaya plugin for Acquia Cloud.
 *
 * @PurgePurger(
 *   id = "alshaya_purge",
 *   label = @Translation("Alshaya - Acquia Cloud"),
 *   configform = "",
 *   cooldown_time = 0.2,
 *   description = @Translation("Invalidates Varnish powered selected load balancers on your Acquia Cloud site."),
 *   multi_instance = FALSE,
 *   types = {"url", "wildcardurl", "tag", "everything"},
 * )
 */
class AlshayaCloudPurger extends AcquiaCloudPurger {

  /**
   * Constructs a AcquiaCloudPurger object.
   *
   * @param \Drupal\acquia_purge\HostingInfoInterface $acquia_purge_hostinginfo
   *   Technical information accessors for the Acquia Cloud environment.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   An HTTP client that can perform remote requests.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(HostingInfoInterface $acquia_purge_hostinginfo, ClientInterface $http_client, ConfigFactoryInterface $config_factory, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($acquia_purge_hostinginfo, $http_client, $configuration, $plugin_id, $plugin_definition);
    $this->client = $http_client;
    $this->hostingInfo = $acquia_purge_hostinginfo;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('acquia_purge.hostinginfo'),
      $container->get('http_client'),
      $container->get('config.factory'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Invalidate a set of tag invalidations.
   *
   * @see \Drupal\purge\Plugin\Purge\Purger\PurgerInterface::invalidate()
   * @see \Drupal\purge\Plugin\Purge\Purger\PurgerInterface::routeTypeToMethod()
   */
  public function invalidateTags(array $invalidations) {
    $this->debug(__METHOD__);

    // Set invalidation states to PROCESSING. Detect tags with spaces in them,
    // as space is the only character Drupal core explicitely forbids in tags.
    foreach ($invalidations as $invalidation) {
      $tag = $invalidation->getExpression();
      if (strpos($tag, ' ') !== FALSE) {
        $invalidation->setState(InvalidationInterface::FAILED);
        $this->logger->error(
          "Tag '%tag' contains a space, this is forbidden.", ['%tag' => $tag]
        );
      }
      else {
        $invalidation->setState(InvalidationInterface::PROCESSING);
      }
    }

    // Create grouped sets of 12 so that we can spread out the BAN load.
    $group = 0;
    $groups = [];
    foreach ($invalidations as $invalidation) {
      if ($invalidation->getState() !== InvalidationInterface::PROCESSING) {
        continue;
      }
      if (!isset($groups[$group])) {
        $groups[$group] = ['tags' => [], ['objects' => []]];
      }
      if (count($groups[$group]['tags']) >= self::TAGS_GROUPED_BY) {
        $group++;
      }
      $groups[$group]['objects'][] = $invalidation;
      $groups[$group]['tags'][] = $invalidation->getExpression();
    }

    // Test if we have at least one group of tag(s) to purge, if not, bail.
    if (!count($groups)) {
      foreach ($invalidations as $invalidation) {
        $invalidation->setState(InvalidationInterface::FAILED);
      }
      return;
    }

    // Now create requests for all groups of tags.
    $site = $this->hostingInfo->getSiteIdentifier();
    $hostnames = $this->configFactory->get('alshaya_purge.settings')->get('hostnames');
    $requests = function () use ($groups, $hostnames, $site) {
      foreach ($groups as $group_id => $group) {
        $tags = implode(' ', Hash::cacheTags($group['tags']));
        foreach ($hostnames as $hostname) {
          yield $group_id => function ($poolopt) use ($site, $tags, $hostname) {
            $opt = [
              'headers' => [
                'X-Acquia-Purge' => $site,
                'X-Acquia-Purge-Tags' => $tags,
                'Accept-Encoding' => 'gzip',
                'User-Agent' => 'Acquia Purge',
              ],
            ];
            if (is_array($poolopt) && count($poolopt)) {
              $opt = array_merge($poolopt, $opt);
            }
            return $this->client->requestAsync('BAN', 'http://' . $hostname . '/tags', $opt);
          };
        }
      }
    };

    // Execute the requests generator and retrieve the results.
    $results = $this->getResultsConcurrently('invalidateTags', $requests);

    // Triage the results and set all invalidation states correspondingly.
    foreach ($groups as $group_id => $group) {
      if ((!isset($results[$group_id])) || (!count($results[$group_id]))) {
        foreach ($group['objects'] as $invalidation) {
          $invalidation->setState(InvalidationInterface::FAILED);
        }
      }
      else {
        if (in_array(FALSE, $results[$group_id])) {
          foreach ($group['objects'] as $invalidation) {
            $invalidation->setState(InvalidationInterface::FAILED);
          }
        }
        else {
          foreach ($group['objects'] as $invalidation) {
            $invalidation->setState(InvalidationInterface::SUCCEEDED);
          }
        }
      }
    }

    $this->debug(__METHOD__);
  }

}
