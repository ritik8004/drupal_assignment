import React from 'react';
import Popup from 'reactjs-popup';
import getCurrencyCode from '../../../../js/utilities/util';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';
import getStringMessage from '../../utilities/strings';

export default class EditEgiftCardAmount extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      remainingAmount: 0
    }
  }

  // Handling validation for the changing the amount of egift card.
  handleValidation = (e) => {
    const { amount } = this.props;
    let errors = false;
    if (document.getElementsByName('egift_amount')[0].value === '' || document.getElementsByName('egift_amount')[0].value <= 0) {
      document.getElementById('egift_amount_error').innerHTML = getStringMessage('form_egift_amount');
      errors = true;
    } else if (document.getElementsByName('egift_amount')[0].value > amount) {
      document.getElementById('egift_amount_error').innerHTML = getStringMessage('egift_insufficient_balance');
      errors = true;
    } else {
      document.getElementById('egift_amount_error').innerHTML = '';
    }

    return errors;
  }

  // Handle the amount update request.
  handleSubmit = (e) => {
    e.preventDefault();
    // Perform validation.
    if (!this.handleValidation(e)) {
      // @todo To perform Amount update.
      const { egift_amount: egiftAmount } = e.target.elements;
      const { cartTotal, handleExceedingAmount } = this.props;
      console.log(egiftAmount);
      if (cartTotal.cart.cart_total > egiftAmount.value){
        handleExceedingAmount(cartTotal.cart.cart_total - egiftAmount.value)
      }
    }
    this.props.closeModal();
    return false;
  }

  handleChange = (e) => {
    const inputAmount = e.target.value;
    const availableAmount = this.props.amount;
    if (e.target.value === '' || e.target.value <= 0) {
      document.getElementById('egift_amount_error').innerHTML = getStringMessage('form_egift_amount');
      errors = true;
    } else if (e.target.value > availableAmount) {
      document.getElementById('egift_amount_error').innerHTML = getStringMessage('egift_insufficient_balance');
      errors = true;
    } else {
      document.getElementById('egift_amount_error').innerHTML = '';
    }
    this.setState(
      { remainingAmount: parseInt(availableAmount) - parseInt(inputAmount) }
    )
  }

  render = () => {
    const {
      closeModal,
      open,
      amount,
    } = this.props;

    const {
      remainingAmount
    } = this.state;

    const currencyCode = getCurrencyCode();

    return (
      <>
        <Popup
          open={open}
          className="egift-amount-update"
          onClose={closeModal}
          closeOnDocumentClick={false}
        >
          <div className="egift-amount-update-wrapper">
            <a className="close" onClick={() => closeModal()}> &times; </a>
            <div className="heading">{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>
            <div className="form-wrapper">
              <form
                className="egift-get-form"
                method="post"
                id="egift-get-form"
                onSubmit={this.handleSubmit}
              >
                {egiftCardHeader({
                  egiftHeading: Drupal.t('Available Balance - @currencyCode @amount', {
                    '@currencyCode': currencyCode,
                    '@amount': amount,
                  }, { context: 'egift' }),
                  egiftSubHeading: Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
                    '@currencyCode': currencyCode,
                    '@remainingAmount': remainingAmount,
                  }, { context: 'egift' }),
                })}
                <span>
                  {currencyCode}
                </span>
                {egiftFormElement({
                  changeHandler: this.handleChange,
                  type: 'number',
                  name: 'amount',
                  className: 'amount',
                })}
                {egiftFormElement({
                  type: 'submit',
                  name: 'button',
                  buttonText: 'Use Amount',
                })}
              </form>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}
