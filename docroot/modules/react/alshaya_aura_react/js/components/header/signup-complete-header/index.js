import React from 'react';
import Cleave from 'cleave.js/react';
import AuraLogo from '../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../alshaya_spc/js/common/components/conditional-view';
import { handleNotYou } from '../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../utilities/aura_utils';

const SignUpCompleteHeader = (props) => {
  const {
    isHeaderModalOpen,
    cardNumber,
    notYouFailed,
    openHeaderModal,
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  return (
    <>
      { isHeaderModalOpen
        && (
        <div className="aura-header-popup-wrapper sign-up-complete">
          <div className="aura-popup-header card-wrapper">
            <div className="heading-section">
              <ConditionalView condition={window.innerWidth < 1024}>
                <AuraLogo stacked="horizontal" />
              </ConditionalView>
              <a className="close-icon" onClick={() => openHeaderModal()}>X</a>
            </div>
            <div className="content-section">
              <div className="title">
                {Drupal.t('Aura account number')}
              </div>
              <Cleave
                name="aura-my-account-link-card"
                className="aura-my-account-link-card"
                disabled
                value={cardNumber}
                options={{ blocks: [4, 4, 4, 4] }}
              />
            </div>
            <div className="footer-section">
              <div className="know-more-wrapper">
                <a href={`${baseUrl}${pathPrefix}user/loyalty-club`}>
                  {Drupal.t(
                    'Know More',
                    {},
                    { context: 'aura' },
                  )}
                </a>
              </div>
              <div className="not-you-wrapper">
                <div className="not-you-loader-placeholder" />
                <div className="error-placeholder" />
                <div
                  className="not-you"
                  onClick={() => handleNotYou(cardNumber)}
                >
                  {getNotYouLabel(notYouFailed)}
                </div>
              </div>
            </div>
          </div>
        </div>
        )}
    </>
  );
};

export default SignUpCompleteHeader;
