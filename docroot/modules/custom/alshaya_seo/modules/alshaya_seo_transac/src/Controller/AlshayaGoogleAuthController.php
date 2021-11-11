<?php

namespace Drupal\alshaya_seo_transac\Controller;

use Drupal\social_auth_google\Controller\GoogleAuthController;

/**
 * Class Alshaya google auth controller.
 */
class AlshayaGoogleAuthController extends GoogleAuthController {

  /**
   * {@inheritdoc}
   */
  public function callback() {
    // Checks if there was an authentication error.
    $redirect = $this->checkAuthError();
    if ($redirect) {
      return $redirect;
    }
    /** @var \League\OAuth2\Client\Provider\GoogleUser|null $profile */
    $profile = $this->processCallback();

    // If authentication was successful.
    if ($profile !== NULL) {
      // Gets (or not) extra initial data.
      user_cookie_save(['alshaya_gtm_user_login_type' => 'Google']);
      $data = $this->userAuthenticator->checkProviderIsAssociated($profile->getId()) ? NULL : $this->providerManager->getExtraDetails();

      return $this->userAuthenticator->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), $profile->getAvatar(), $data);
    }

    return $this->redirect('user.login');

  }

}
