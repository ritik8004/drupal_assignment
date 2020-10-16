import React from 'react';
import Popup from 'reactjs-popup';
import AuraLogo from '../../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import Loading from '../../../../../../alshaya_spc/js/utilities/loading';
import WithModal
  from '../../../../../../alshaya_spc/js/checkout/components/with-modal';

const AuraFormSignUpOTPModal = React.lazy(
  () => import('../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form'),
);

const AuraMyAccountNoLinkedCard = () => (
  <div className="aura-myaccount-no-linked-card-wrapper no-card-found fadeInUp">
    <div className="aura-logo">
      <ConditionalView condition={window.innerWidth > 1024}>
        <AuraLogo stacked="vertical" />
      </ConditionalView>
      <ConditionalView condition={window.innerWidth < 1025}>
        <AuraLogo stacked="horizontal" />
      </ConditionalView>
    </div>
    <div className="aura-myaccount-no-linked-card-description no-card-found">
      <div className="link-your-card">
        { Drupal.t('Already AURA Member?') }
        <div className="btn">
          { Drupal.t('Link your card') }
        </div>
      </div>
      <div className="sign-up">
        { Drupal.t('Ready to be Rewarded?') }
        <WithModal modalStatusKey="aura-modal-myaccount-otp">
          {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
            <>
              <div
                className="btn"
                onClick={() => triggerOpenModal()}
              >
                { Drupal.t('Sign up') }
              </div>
              <Popup
                className="aura-modal-form otp-modal"
                open={isModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <React.Suspense fallback={<Loading />}>
                  <AuraFormSignUpOTPModal
                    closeModal={triggerCloseModal}
                  />
                </React.Suspense>
              </Popup>
            </>
          )}
        </WithModal>
      </div>
    </div>
  </div>
);

export default AuraMyAccountNoLinkedCard;
