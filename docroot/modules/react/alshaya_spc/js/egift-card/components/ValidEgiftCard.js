import React from 'react';
import ConditionalView from '../../../../alshaya_algolia_react/js/common/components/conditional-view';
import { egiftCardHeader } from '../../utilities/egift_util';
import UpdateEgiftCard from './UpdateEgiftCard';

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
    // @todo To call API to update the amount.
    this.setState({
      amount: updateAmount,
    });
    this.closeModal();
  }

  render = () => {
    const { open, amount, remainingAmount } = this.state;
    const { currency_config: currencySettings } = drupalSettings.alshaya_spc;

    return (
      <div className="egift-wrapper">
        {egiftCardHeader({
          egiftHeading: Drupal.t('Applied card amount - @currencyCode @amount', {
            '@currencyCode': currencySettings.currency_code,
            '@amount': amount,
          }, { context: 'egift' }),
          egiftSubHeading: Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
            '@currencyCode': currencySettings.currency_code,
            '@remainingAmount': remainingAmount,
          }, { context: 'egift' }),
        })}
        <ConditionalView conditional={open}>
          <UpdateEgiftCard
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

        <input type="checkbox" id="link-egift-card" onChange={this.handleCardLink} />
        <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
      </div>
    );
  }
}
