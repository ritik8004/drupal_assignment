<?php

namespace Drupal\alshaya_bazaar_voice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
  public function getWriteReviewFieldsConfig() {
    $configData = $this->alshayaBazaarVoice->getWriteReviewFieldsConfig();

    $response = new JsonResponse();
    $response->setData(array_values($configData));

    return $response;
  }

  /**
   * Convert base64 content into image and upload temporarily.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return array
   *   Build array.
   */
  public function uploadFile(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $data_url = $request_content['dataUrl'];
    // Replace space with hyphen to resolve upload issue.
    $file_name = str_replace(" ", "-", $request_content['fileName']);

    $review_photo_temp_upload = 'public://review_photo_temp_upload';
    // Make sure the directory exists and is writable.
    $this->fileSystem->prepareDirectory($review_photo_temp_upload, FileSystemInterface::CREATE_DIRECTORY);
    $image_name = $review_photo_temp_upload . '/' . $file_name;

    $file_object = $this->fileSystem->saveData(base64_decode($data_url), $image_name, FileSystemInterface::EXISTS_REPLACE);
    $url = file_create_url($file_object);

    $response = new JsonResponse();
    $response->setData($url);

    return $response;
  }

  /**
   * Returns UAS token to be used for site authenticated user.
   *
   * @return string
   *   UAS Token.
   */
  public function getUasToken() {
    $uasToken = $this->alshayaBazaarVoice->generateEncodedUasToken();

    $response = new JsonResponse();
    $response->setData($uasToken);

    return $response;
  }

  /**
   * Returns review stats of a product.
   *
   * @param string $productId
   *   Product id or sanitized sku id.
   *
   * @return array
   *   Build array.
   */
  public function getProductReviewStatistics(string $productId) {
    // Add user review of current product in user settings.
    $reviewStatsData = $this->alshayaBazaarVoice->getProductReviewStatistics($productId);
    return new JsonResponse(!empty($reviewStatsData) ? reset($reviewStatsData) : []);
  }

  /**
   * Returns write a revivew form coming from Bazaarvoice.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return array
   *   Build array.
   */
  public function pieWriteReviewContainer(Request $request) {
    $build = [];
    $bvaction = $request->query->get('bvaction');
    $bvproductId = $request->query->get('bvproductId');

    if (empty($bvaction) || empty($bvproductId)) {
      throw new NotFoundHttpException();
    }

    $bvPageType = [
      '#tag' => 'meta',
      '#attributes' => [
        'name' => 'bv:pageType',
        'content' => 'container',
      ],
    ];
    $robots = [
      '#tag' => 'meta',
      '#attributes' => [
        'name' => 'robots',
        'content' => 'noindex, nofollow',
      ],
    ];

    $build['#attached']['html_head'][] = [$robots, 'robots'];
    $build['#attached']['html_head'][] = [$bvPageType, 'bv:pageType'];
    $build['#attached']['library'][] = 'bazaar_voice/bazaar_voice';
    $build['#attached']['library'][] = 'alshaya_bazaar_voice/pie_write_review';

    return $build;
  }

}
