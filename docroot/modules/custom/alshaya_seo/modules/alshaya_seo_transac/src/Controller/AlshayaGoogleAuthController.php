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
      // Set an additional cookie to utilise once on FE to perform some action.
      // Example, we use this cookie to enable wishlist merge once on FE and
      // then remove this in second time. This is because, first time page loads
      // in the social callback popup where we need to avoid such actions.
      user_cookie_save(['alshaya_user_login_type' => 'social_login']);
      $data = $this->userAuthenticator->checkProviderIsAssociated($profile->getId()) ? NULL : $this->providerManager->getExtraDetails();

      return $this->userAuthenticator->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), $profile->getAvatar(), $data);
    }

    return $this->redirect('user.login');

  }

}
