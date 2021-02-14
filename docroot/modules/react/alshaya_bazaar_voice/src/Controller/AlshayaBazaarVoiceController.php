<?php

namespace Drupal\alshaya_bazaar_voice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Symfony\Component\HttpFoundation\Request;

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
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * AlshayaBazaarVoiceController constructor.
   *
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice Helper.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The filesystem service.
   */
  public function __construct(AlshayaBazaarVoice $alshaya_bazaar_voice,
                              FileSystemInterface $file_system) {
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_bazaar_voice.service'),
      $container->get('file_system')
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

  /**
   * Returns write a review form configs synced from BazaarVoice config hub.
   *
   * @return array
   *   Build array.
   */
  public function uploadFile(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $dataUrl = $request_content['dataUrl'];
    $fileName = $request_content['fileName'];

    $review_photo_temp_upload = 'public://review_photo_temp_upload';
    // Make sure the directory exists and is writable.
    $this->fileSystem->prepareDirectory($review_photo_temp_upload, FileSystemInterface::CREATE_DIRECTORY);
    $image_name = $review_photo_temp_upload . '/' . $fileName;

    $file_object = $this->fileSystem->saveData(base64_decode($dataUrl), $image_name, FileSystemInterface::EXISTS_REPLACE);
    $url = file_create_url($file_object);

    $response = new JsonResponse();
    $response->setData($url);

    return $response;
  }

}
