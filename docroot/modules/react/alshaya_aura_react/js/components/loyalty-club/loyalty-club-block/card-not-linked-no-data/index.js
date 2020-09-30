import React from 'react';
import AuraLogo from '../../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';

const AuraMyAccountNoLinkedCard = () => (
  <div className="aura-myaccount-not-linked-card-wrapper">
    <div className="aura-logo">
      <ConditionalView condition={window.innerWidth > 1024}>
        <AuraLogo stacked="vertical" />
      </ConditionalView>
      <ConditionalView condition={window.innerWidth < 1025}>
        <AuraLogo stacked="horizontal" />
      </ConditionalView>
    </div>
    <div className="aura-myaccount-not-linked-card-description">
      <div className="link-your-card">
        { Drupal.t('Already AURA Member?') }
        <a href="">
          { Drupal.t('Link your card') }
        </a>
      </div>
      <div className="sign-up">
        { Drupal.t('Ready to be Rewarded?') }
        <a href="">
          { Drupal.t('Sign up') }
        </a>
      </div>
    </div>
  </div>
);

export default AuraMyAccountNoLinkedCard;
