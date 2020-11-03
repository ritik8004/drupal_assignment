import React from 'react';
import Cleave from 'cleave.js/react';
import AuraLogo from '../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../alshaya_spc/js/common/components/conditional-view';

const SignUpCompleteHeader = (props) => {
  const {
    isHeaderModalOpen,
    handleNotYou,
    cardNumber,
    noRegisterLinks,
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  return (
    <>
      { isHeaderModalOpen
        && (
        <div className="aura-header-popup-wrapper sign-up-complete">
          <div className="aura-popup-header card-wrapper">
            <ConditionalView condition={window.innerWidth < 1024}>
              <AuraLogo stacked="horizontal" />
            </ConditionalView>
            <div className="title">
              {Drupal.t('Your Aura card number')}
            </div>
            <Cleave
              name="aura-my-account-link-card"
              className="aura-my-account-link-card"
              disabled
              value={cardNumber}
              options={{ blocks: [4, 4, 4, 4] }}
            />
            <div
              className="not-you"
              onClick={handleNotYou}
            >
              { Drupal.t('Not you?') }
            </div>
          </div>
          {
            !noRegisterLinks
              && (
                <>
                  <div className="aura-popup-body">
                    <p>{Drupal.t('To spend your points you will have to sign in or create an account.')}</p>
                  </div>
                  <div className="aura-popup-footer">
                    <a
                      className="create-an-account"
                      href={`${baseUrl}${pathPrefix}user/register`}
                    >
                      {Drupal.t('Create an account')}
                    </a>
                    <a
                      href={`${baseUrl}${pathPrefix}user/login`}
                      className="sign-in"
                    >
                      {Drupal.t('Sign in')}
                    </a>
                  </div>
                </>
              )
          }
        </div>
        )}
    </>
  );
};

export default SignUpCompleteHeader;
