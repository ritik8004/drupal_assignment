<?php

namespace Drupal\alshaya_bazaar_voice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

/**
 * Alshaya BazaarVoice Controller.
 */
class AlshayaBazaarVoiceController extends ControllerBase {

  /**
   * Alshaya BazaarVoice Helper.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * AlshayaBazaarVoiceController constructor.
   *
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice Helper.
   */
  public function __construct(AlshayaBazaarVoice $alshaya_bazaar_voice) {
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_bazaar_voice.service')
    );
  }

  /**
   * Returns write a review form configs synced from BazaarVoice config hub.
   *
   * @return array
   *   Build array.
   */
  public function getBazaarVoiceFormConfig() {
    $configData = $this->alshayaBazaarVoice->getBazaarVoiceFormConfig();

    $response = new JsonResponse();
    $response->setData(array_values($configData));

    return $response;
  }

}
