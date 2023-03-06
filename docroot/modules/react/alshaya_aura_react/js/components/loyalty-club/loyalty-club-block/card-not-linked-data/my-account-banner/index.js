import React from 'react';
import Collapsible from 'react-collapsible';
import Cleave from 'cleave.js/react';
import AuraLogo from '../../../../../svg-component/aura-logo';
import {
  handleLinkYourCard,
  handleNotYou,
} from '../../../../../utilities/cta_helper';
import { getNotYouLabel } from '../../../../../utilities/aura_utils';

class MyAccountBanner extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      collapsibleClass: 'closed',
    };
  }

  /**
   * Get accordion header content for aura banner.
   */
  getAuraWrapperHeader = () => (
    <div className="header">
      { Drupal.t('An Aura loyalty card is already associated with your email address. It just takes one click to link.', {}, { context: 'aura' }) }
      <span className="bold">{Drupal.t('Do you want to link now?', {}, { context: 'aura' })}</span>
      <span className="aura-myaccount-no-linked-card-accordion-icon" />
    </div>
  );

  /**
   * Set collapsible class for banner content.
   *
   * @param className
   *   Class name as per collapsible state.
   */
  setCollapsibleClass = (className) => {
    this.setState({
      collapsibleClass: className,
    });
  }

  render() {
    const { cardNumber, notYouFailed, tier } = this.props;
    const tierClass = tier || 'no-tier';
    const { collapsibleClass } = this.state;

    return (
      <div className={`aura-myaccount-no-linked-card-wrapper ${collapsibleClass} old-card-found fadeInUp aura-level-${tierClass}`}>
        <div className="aura-logo">
          <AuraLogo />
        </div>
        <div className="aura-myaccount-no-linked-card-description old-card-found">
          <Collapsible
            trigger={this.getAuraWrapperHeader()}
            open={false}
            onOpening={() => this.setCollapsibleClass('open')}
            onClosing={() => this.setCollapsibleClass('closed')}
          >
            <div className="my-account-aura-card-wrapper">
              <div className="card-number-wrapper">
                <div className="card-number-label">
                  { Drupal.t('Aura membership number', {}, { context: 'aura' })}
                </div>
                <Cleave
                  name="aura-my-account-link-card"
                  className="aura-my-account-link-card"
                  disabled
                  value={cardNumber}
                  options={{ blocks: [4, 4, 4, 4] }}
                />
              </div>
              <div className="link-card-wrapper">
                <div className="not-you-wrapper">
                  <div className="not-you-loader-placeholder" />
                  <div className="error-placeholder" />
                  <div
                    className="not-you"
                    onClick={() => {
                      handleNotYou(cardNumber);
                      Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
                    }}
                  >
                    { getNotYouLabel(notYouFailed) }
                  </div>
                </div>
                <div className="link-card-loader-placeholder" />
                <div
                  className="link-your-card"
                  onClick={() => {
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                    handleLinkYourCard(cardNumber);
                  }}
                >
                  { Drupal.t('Link your account') }
                </div>
              </div>
            </div>
          </Collapsible>
        </div>
      </div>
    );
  }
}

export default MyAccountBanner;
