import React from 'react';
import ConditionalView from '../../common/components/conditional-view';
import { egiftCardHeader, getCurrencyCode } from '../../utilities/egift_util';
import UpdateEgiftCardAmount from './UpdateEgiftCardAmount';

export default class ValidEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      amount: 0,
      remainingAmount: 0,
    };
  }

  componentDidMount = () => {
    // @todo get amount and update the state.
    this.setState({
      amount: 280,
      remainingAmount: 220.0,
    });
  }

  openModal = (e) => {
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  // Handle remove card.
  handleRemoveCard = () => {

  }

  // Update the user account with egift card.
  handleCardLink = () => {

  }

  // Update egift amount.
  handleAmountUpdate = (updateAmount) => {
    // Prepare the request object for redeem API.
    const postData = {
      redeem_points: {
        action: 'set_points',
        quote_id: '28288',
        amount: updateAmount,
        card_number: '4250120656063430',
        payment_method: 'hps_payment',
      },
    };
    this.setState({
      amount: updateAmount,
    });
    // Proceed only if postData object is available.
    if (postData) {
      // @todo To call API to update the amount.
    }
    this.closeModal();
  }

  // get User session and linked card status.
  getUserEgiftStatus = () => {
    const validUser = drupalSettings.user.uid > 0;
    // @todo To check if user is already having a linked card.

    return validUser;
  }

  render = () => {
    const { open, amount, remainingAmount } = this.state;
    const currencyCode = getCurrencyCode();

    return (
      <div className="egift-wrapper">
        {egiftCardHeader({
          egiftHeading: Drupal.t('Applied card amount - @currencyCode @amount', {
            '@currencyCode': currencyCode,
            '@amount': amount,
          }, { context: 'egift' }),
          egiftSubHeading: Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
            '@currencyCode': currencyCode,
            '@remainingAmount': remainingAmount,
          }, { context: 'egift' }),
        })}
        <ConditionalView conditional={open}>
          <UpdateEgiftCardAmount
            closeModal={this.closeModal}
            open={open}
            amount={amount}
            remainingAmount={remainingAmount}
            updateAmount={this.handleAmountUpdate}
          />
        </ConditionalView>
        <div className="remove-egift-card">
          <button type="button" onClick={this.handleRemoveCard}>{Drupal.t('Remove', {}, { context: 'egift' })}</button>
        </div>
        <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
        <ConditionalView condition={this.getUserEgiftStatus()}>
          <input type="checkbox" id="link-egift-card" onChange={this.handleCardLink} />
          <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
        </ConditionalView>
      </div>
    );
  }
}
