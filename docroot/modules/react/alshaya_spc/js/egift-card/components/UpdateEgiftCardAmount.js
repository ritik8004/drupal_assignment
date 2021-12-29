import React from 'react';
import Popup from 'reactjs-popup';
import getCurrencyCode from '../../../../js/utilities/util';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';
import getStringMessage from '../../../../js/utilities/strings';

export default class UpdateEgiftCardAmount extends React.Component {
  // Handling validation for the changing the amount of egift card.
  handleValidation = (e) => {
    const { value: egiftAmount } = e.target.elements.egift_amount;
    const { amount, remainingAmount, cart } = this.props;

    let errors = false;
    let message = '';
    // Proceed only if user has entered some value.
    if (egiftAmount.length === 0) {
      message = getStringMessage('form_egift_amount');
      errors = true;
    } else if (remainingAmount && (egiftAmount > (amount + remainingAmount))) {
      message = getStringMessage('egift_insufficient_balance');
      errors = true;
    } else if (egiftAmount <= 0) {
      message = getStringMessage('egift_valid_amount');
      errors = true;
    } else if (egiftAmount > cart.cart_total) {
      message = Drupal.t('Redeem amount should be less than or equal to the cart value.');
      errors = true;
    }

    document.getElementById('egift_amount_error').innerHTML = message;

    return errors;
  }

  // Handle the amount update request.
  handleSubmit = (e) => {
    e.preventDefault();
    // Perform validation.
    if (!this.handleValidation(e)) {
      // @todo To perform Amount update.
      const { egift_amount: egiftAmount } = e.target.elements;
      const { updateAmount } = this.props;
      // Display the message based on update status.
      const updateStatus = updateAmount(egiftAmount.value);
      if (!updateStatus) {
        document.getElementById('egift_amount_error').innerHTML = getStringMessage('egift_amount_update_failed');
      }
    }

    return false;
  }

  render = () => {
    const {
      closeModal,
      open,
      amount,
      remainingAmount,
    } = this.props;

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
            <div className="heading spc-checkout-section-title">{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>
            <div className="form-wrapper">
              <form
                className="egift-get-form"
                method="post"
                id="egift-get-form"
                onSubmit={this.handleSubmit}
              >
                {egiftCardHeader({
                  egiftHeading: Drupal.t('Applied card amount - @currencyCode @amount', {
                    '@currencyCode': currencyCode,
                    '@amount': amount,
                  }, { context: 'egift' }),
                  egiftSubHeading: (remainingAmount ? Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
                    '@currencyCode': currencyCode,
                    '@remainingAmount': remainingAmount,
                  }, { context: 'egift' }) : ''),
                })}
                {egiftFormElement({
                  type: 'number',
                  name: 'amount',
                  className: 'amount',
                  label: 'Amount',
                  value: amount,
                })}
                <div className="egift-submit-btn-wrapper">
                  {egiftFormElement({
                    type: 'submit',
                    name: 'button',
                    buttonText: 'Edit Amount',
                  })}
                </div>
              </form>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}
