import React from 'react';
import AuraFormLinkCard from '../../../aura-forms/aura-link-card-textbox';
import LinkYourCardMessage from '../link-your-card-message';
import ConditionalView from '../../../../../common/components/conditional-view';
import { getMembersToEarnMessage } from '../../../utilities/checkout_helper';
import getStringMessage from '../../../../../../../js/utilities/strings';
import ToolTip from '../../../../../utilities/tooltip';

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
    const { pointsToEarn, cartId, formActive } = this.props;
    const { showLinkCardMessage } = this.state;

    // Add active or in-active class based on the formActive flag.
    const active = formActive ? 'active' : 'in-active';

    return (
      <div className="block-content guest-user">
        <div className="title">
          <div className="subtitle-1">
            { getStringMessage('checkout_earn_and_redeem') }
            <ToolTip enable question>{ getStringMessage('checkout_earn_and_redeem_tooltip') }</ToolTip>
          </div>
          <div className="subtitle-2">{ getMembersToEarnMessage(pointsToEarn) }</div>
        </div>
        <div className={`spc-aura-link-card-form ${active}`}>
          <div className="label">
            { getStringMessage('checkout_already_member_question') }
            <ToolTip enable question>{ getStringMessage('checkout_already_member_question_tooltip') }</ToolTip>
          </div>
          <div className="item-wrapper">
            <AuraFormLinkCard
              enableShowLinkCardMessage={this.enableShowLinkCardMessage}
              cartId={cartId}
              formActive={formActive}
            />
          </div>
        </div>
        <ConditionalView condition={showLinkCardMessage}>
          <LinkYourCardMessage />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraNotLinkedNoDataCheckout;
