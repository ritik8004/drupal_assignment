import React from 'react';
import Cleave from 'cleave.js/react';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraLogo from '../../../../svg-component/aura-logo';

export default class AuraMyAccountOldCardFound extends React.Component {
  handleNotYou = () => {
    const { cardNumber } = this.props;
    const { handleNotYou } = this.props;
    handleNotYou(cardNumber);
  }

  handleLinkYourCardClick = () => {
    const { cardNumber } = this.props;
    const { handleLinkYourCardClick } = this.props;
    handleLinkYourCardClick(cardNumber);
  }

  render() {
    const { cardNumber } = this.props;

    return (
      <div className="aura-myaccount-no-linked-card-wrapper old-card-found">
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
            { Drupal.t('An Aura loyalty card is associate with your email address. It just a takes one click to link.') }
            <span className="bold">{Drupal.t('Do you want to link now?')}</span>
          </div>
          <div className="card-number-wrapper">
            <Cleave
              name="aura-my-account-link-card"
              className="aura-my-account-link-card"
              disabled
              value={cardNumber}
              options={{ blocks: [4, 4, 4, 4] }}
            />
            <div
              className="link-your-card"
              onClick={this.handleLinkYourCardClick}
            >
              { Drupal.t('Link your card') }
            </div>
            <div
              className="not-you"
              onClick={this.handleNotYou}
            >
              { Drupal.t('Not you?') }
            </div>
          </div>
        </div>
      </div>
    );
  }
}
