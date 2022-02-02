<?php

namespace Drupal\alshaya_seo_transac\Controller;

use Drupal\social_auth_facebook\Controller\FacebookAuthController;

/**
 * Class Alshaya FB auth controller.
 */
class AlshayaFBAuthController extends FacebookAuthController {

  /**
   * {@inheritdoc}
   */
  public function callback() {
    // Checks if there was an authentication error.
    $redirect = $this->checkAuthError();
    if ($redirect) {
      return $redirect;
    }
    /** @var \League\OAuth2\Client\Provider\FacebookUser|null $profile */
    $profile = $this->processCallback();

    // If authentication was successful.
    if ($profile) {
      // Check for email.
      if (!$profile->getEmail()) {
        $this->messenger->addError($this->t('Facebook authentication failed. This site requires permission to get your email address.'));

        return $this->redirect('user.login');
      }
      user_cookie_save(['alshaya_gtm_user_login_type' => 'Facebook']);
      // Gets (or not) extra initial data.
      $data = $this->userAuthenticator->checkProviderIsAssociated($profile->getId()) ? NULL : $this->providerManager->getExtraDetails();

      // If user information could be retrieved.
      return $this->userAuthenticator->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), $profile->getPictureUrl(), $data);

    }

    return $this->redirect('user.login');

  }

}
