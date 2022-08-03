<?php

namespace Drupal\alshaya_bazaar_voice\Plugin\rest\resource;

use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\node\NodeInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get configurations related to BazaarVoice feature.
 *
 * @RestResource(
 *   id = "bazaar_voice_configs",
 *   label = @Translation("BazaarVoice Configs"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/bv/configs"
 *   }
 * )
 */
class BazaarVoiceConfigResource extends ResourceBase {

  /**
   * Node bundle machine name.
   */
  public const NODE_TYPE = 'advanced_page';

  /**
   * Alshaya BazaarVoice Service.
   *
   * @var Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * ProductResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice service.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AlshayaBazaarVoice $alshaya_bazaar_voice,
    MobileAppUtility $mobile_app_utility
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
    $this->mobileAppUtility = $mobile_app_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_bazaar_voice.service'),
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns response data for BazaarVoice configurations.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing BazaarVoice configurations.
   */
  public function get() {
    $data = [];
    if (empty($this->alshayaBazaarVoice->getBasicConfigurations('mapp'))) {
      $this->mobileAppUtility->throwException();
    }

    $data['basic'] = $this->alshayaBazaarVoice->getBasicConfigurations('mapp');

    if (!empty($data['basic']['write_review_tnc'])) {
      $node = $this->mobileAppUtility->getNodeFromAlias($data['basic']['write_review_tnc'], self::NODE_TYPE);
      if ($node instanceof NodeInterface) {
        $data['basic']['write_review_tnc'] = $this->mobileAppUtility->getDeepLink($node);
      }
    }
    if (!empty($data['basic']['write_review_guidlines'])) {
      $node = $this->mobileAppUtility->getNodeFromAlias($data['basic']['write_review_guidlines'], self::NODE_TYPE);
      if ($node instanceof NodeInterface) {
        $data['basic']['write_review_guidlines'] = $this->mobileAppUtility->getDeepLink($node);
      }
    }
    if (!empty($data['basic']['comment_form_tnc'])) {
      $node = $this->mobileAppUtility->getNodeFromAlias($data['basic']['comment_form_tnc'], self::NODE_TYPE);
      if ($node instanceof NodeInterface) {
        $data['basic']['comment_form_tnc'] = $this->mobileAppUtility->getDeepLink($node);
      }
    }
    $data['sorting_options'] = $this->alshayaBazaarVoice->getSortingOptions();
    $data['pdp_filter_options'] = $this->alshayaBazaarVoice->getPdpFilterOptions();
    $data['bv_error_messages'] = $this->alshayaBazaarVoice->getBazaarVoiceErrorMessages();
    $response_data = $data;

    return new ModifiedResourceResponse($response_data);
  }

}
