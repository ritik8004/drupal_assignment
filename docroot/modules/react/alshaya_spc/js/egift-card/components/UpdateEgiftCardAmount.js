import React from 'react';
import Popup from 'reactjs-popup';
import getCurrencyCode from '../../../../js/utilities/util';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';
import getStringMessage from '../../../../js/utilities/strings';

export default class UpdateEgiftCardAmount extends React.Component {
  // Handling validation for the changing the amount of egift card.
  handleValidation = (e) => {
    const { egift_amount: egiftAmount } = e.target.elements;
    const { amount, remainingAmount } = this.props;

    let errors = false;
    // Proceed only if user has entered some value.
    if (egiftAmount.value.length === 0) {
      document.getElementById('egift_amount_error').innerHTML = getStringMessage('form_egift_amount');
      errors = true;
    } else if (egiftAmount.value > (amount + remainingAmount)) {
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
      const { updateAmount } = this.props;
      updateAmount(egiftAmount.value);
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
            <div className="heading">{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</div>
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
                  egiftSubHeading: Drupal.t('Remaining Balance - @currencyCode @remainingAmount', {
                    '@currencyCode': currencyCode,
                    '@remainingAmount': remainingAmount,
                  }, { context: 'egift' }),
                })}
                <span>
                  {currencyCode}
                </span>
                {egiftFormElement({
                  type: 'number',
                  name: 'amount',
                  className: 'amount',
                })}
                {egiftFormElement({
                  type: 'submit',
                  name: 'button',
                  buttonText: 'Edit Amount',
                })}
              </form>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}
