import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import EgiftCardNotLinked from '../my-account-egift-card-not-linked';
import getStringMessage from '../../../../../js/utilities/strings';

class EgiftCardLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      linkedCard: true,
      removeCard: true,
    };
  }

  removeCardAction = (removeCard) => {
    if (removeCard === true) {
      this.setState({
        linkedCard: false,
      });
    }
  };

  render() {
    const {
      linkedCard,
      removeCard,
    } = this.state;

    return (
      <div className="egift-card-linked-wrapper">
        <ConditionalView condition={linkedCard === true}>
          <div className="egift-card-linked-wrapper-top">
            <div className="egift-linked-thumbnail"> Image </div>
            <div className="egift-linked-title">{`${getStringMessage('my_efit_card')}`}</div>
            <div className="egift-linked-balance">Balance: KWD 25.00</div>
            <button id="egift-remove-button" type="button" className="egift-card-remove" onClick={() => { this.removeCardAction(removeCard); }}>{Drupal.t('Remove')}</button>
          </div>
          <div className="egift-card-linked-wrapper-bottom">
            <div className="egift-linked-card-number">{`${getStringMessage('gift_card_number_label')}`}</div>
            <div className="egift-linked-card-number-value">1234 1234 1234 1234</div>
            <div className="egift-linked-expires">{`${getStringMessage('expires_label')}`}</div>
            <div className="egift-linked-expires-value">24th,Aug 2022</div>
            <div className="egift-linked-card-type">{`${getStringMessage('card_type_label')}`}</div>
            <div className="egift-linked-card-type-value">Alshaya Classic Card</div>
            <button id="egift-topup-button" type="button" className="egift-topup">{`${getStringMessage('top_up')}`}</button>
          </div>
        </ConditionalView>

        <ConditionalView condition={linkedCard === false}>
          <EgiftCardNotLinked />
        </ConditionalView>
      </div>
    );
  }
}

export default EgiftCardLinked;
