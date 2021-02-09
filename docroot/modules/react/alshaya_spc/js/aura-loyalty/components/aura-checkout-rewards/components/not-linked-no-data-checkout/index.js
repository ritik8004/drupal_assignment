import React from 'react';
import AuraFormLinkCard from '../../../aura-forms/aura-link-card-textbox';
import LinkYourCardMessage from '../link-you-card-message';
import ConditionalView from '../../../../../common/components/conditional-view';
import { getMembersToEarnMessage } from '../../../utilities/checkout_helper';
import getStringMessage from '../../../../../../../js/utilities/strings';

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

  render() {
    const { price, cartId } = this.props;
    const { showLinkCardMessage } = this.state;

    return (
      <div className="block-content guest-user">
        <div className="title">
          <div className="subtitle-1">{ getStringMessage('checkout_earn_and_redeem') }</div>
          <div className="subtitle-2">{ getMembersToEarnMessage(price) }</div>
        </div>
        <div className="spc-aura-link-card-form">
          <div className="label">{ getStringMessage('checkout_already_member_question') }</div>
          <div className="item-wrapper">
            <AuraFormLinkCard
              enableShowLinkCardMessage={this.enableShowLinkCardMessage}
              cartId={cartId}
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
