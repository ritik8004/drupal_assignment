import React from 'react';
import Popup from 'reactjs-popup';
import AuraLogo from '../../../../../svg-component/aura-logo';
import {
  handleLinkYourCard,
} from '../../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../../utilities/aura_utils';
import ConditionalView
  from '../../../../../../../js/utilities/components/conditional-view';
import AuraFormLinkCardOTPModal
  from '../../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';

class MyAuraBanner extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      chosenCountryCode: null,
      isLinkCardModalOpen: false,
    };
  }

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  /**
   * Toggles OTP/Join now Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the OTP/Join now Modal.
   *   And hide the Link card Modal.
   */
  toggleOTPModal = (toggle) => {
    this.setState({
      isLinkCardModalOpen: !toggle,
    });
  };

  /**
   * Toggles Link card Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the link card Modal.
   *   And hide the New User Modal.
   */
  toggleLinkCardModal = (toggle) => {
    this.setState({
      isLinkCardModalOpen: toggle,
    });
  };

  render() {
    const {
      isLinkCardModalOpen,
      chosenCountryCode,
    } = this.state;
    const { cardNumber, notYouFailed, tier } = this.props;
    const tierClass = tier || 'no-tier';

    return (
      <div className={`aura-myaccount-no-linked-card-wrapper old-card-found fadeInUp aura-level-${tierClass}`}>
        <div className="aura-logo">
          <ConditionalView condition={window.innerWidth > 1024}>
            <AuraLogo stacked="vertical" />
          </ConditionalView>
          <ConditionalView condition={window.innerWidth < 1025}>
            <AuraLogo stacked="horizontal" />
          </ConditionalView>
        </div>
        <div className="aura-myaccount-no-linked-card-description old-card-found">
          <div className="header">
            <span className="bold">{ `${drupalSettings.userDetails.userName} `}</span>
            {Drupal.t('an Aura loyalty account no. @card_number is associated with your email adress. It just takes one click to link.', {
              '@card_number': cardNumber,
            }, { context: 'aura' })}
            <span className="bold">{Drupal.t('Do you want to link now?')}</span>
          </div>
          <div className="card-number-wrapper">
            <div className="link-card-wrapper">
              <div className="link-card-loader-placeholder" />
              <div
                className="link-your-card"
                onClick={() => {
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                  handleLinkYourCard(cardNumber);
                }}
              >
                { Drupal.t('Link your card', {}, { context: 'aura' }) }
              </div>
            </div>
            <div className="not-you-wrapper">
              <div className="not-you-loader-placeholder" />
              <div className="error-placeholder" />
              <div
                className="not-you"
                onClick={() => {
                  this.toggleLinkCardModal(true);
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
                }}
              >
                { getNotYouLabel(notYouFailed) }
              </div>
              <Popup
                className="aura-modal-form link-card-otp-modal"
                open={isLinkCardModalOpen}
                closeOnEscape={false}
                closeOnDocumentClick={false}
              >
                <AuraFormLinkCardOTPModal
                  closeLinkCardOTPModal={() => this.setState({
                    isLinkCardModalOpen: false,
                  })}
                  setChosenCountryCode={this.setChosenCountryCode}
                  chosenCountryCode={chosenCountryCode}
                  openOTPModal={() => this.toggleOTPModal(true)}
                />
              </Popup>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default MyAuraBanner;
