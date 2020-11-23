import React from 'react';
import AuraFormLinkCard from '../../../aura-forms/aura-link-card-textbox';
import PointsString from '../../../utilities/points-string';
import LinkYourCardMessage from '../link-you-card-message';
import ConditionalView from '../../../../../common/components/conditional-view';

class AuraNotLinkedNoDataCheckout extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showLinkCardMessage: false,
    };
  }

  // State setter for link card component flag.
  enableShowLinkCardMessage = () => {
    // We do this only for registered in users.
    if (drupalSettings.user.uid > 0) {
      this.setState({
        showLinkCardMessage: true,
      });
    }
  };

  getMembersToEarnMessage = (points) => {
    const toEarnMessageP1 = `${Drupal.t('Members will earn')} `;
    const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

    return (
      <span className="spc-checkout-aura-points-to-earn">
        { toEarnMessageP1 }
        <PointsString points={points} />
        { toEarnMessageP2 }
      </span>
    );
  };

  render() {
    const { pointsToEarn } = this.props;
    const { showLinkCardMessage } = this.state;

    return (
      <div className="block-content guest-user">
        <div className="title">
          <div className="subtitle-1">{ Drupal.t('Earn and redeem as you shop ') }</div>
          <div className="subtitle-2">{ this.getMembersToEarnMessage(pointsToEarn) }</div>
        </div>
        <div className="spc-aura-link-card-form">
          <div className="label">{ Drupal.t('Already an Aura member?') }</div>
          <div className="item-wrapper">
            <AuraFormLinkCard
              enableShowLinkCardMessage={this.enableShowLinkCardMessage}
            />
          </div>
        </div>
        <ConditionalView condition={showLinkCardMessage === true}>
          <LinkYourCardMessage />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraNotLinkedNoDataCheckout;
