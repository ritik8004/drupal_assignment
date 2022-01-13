import React from 'react';
import Popup from 'reactjs-popup';
import { egiftCardHeader, egiftFormElement } from '../../utilities/egift_util';
import getStringMessage from '../../../../js/utilities/strings';
import PriceElement from '../../utilities/special-price/PriceElement';
import { getAmountWithCurrency } from '../../utilities/checkout_util';

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
      message = Drupal.t('Redeem amount should be less than or equal to the cart total.', {}, { context: 'egift' });
      errors = true;
    }

    document.getElementById('egift_amount_error').innerHTML = message;

    return errors;
  }

  // Handle the amount update request.
  handleSubmit = async (e) => {
    e.preventDefault();
    // Perform validation.
    if (!this.handleValidation(e)) {
      const { egift_amount: egiftAmount } = e.target.elements;
      const { updateAmount } = this.props;
      // Display the message based on update status.
      const result = await updateAmount(egiftAmount.value);
      if (result.error) {
        document.getElementById('egift_amount_error').innerHTML = result.message;
      }
    }
  }

  render = () => {
    const {
      closeModal,
      open,
      amount,
      remainingAmount,
    } = this.props;

    const appliedAmount = (
      <span>
        {Drupal.t('Applied card amount - ', {}, { context: 'egift' })}
        <PriceElement amount={amount} format="string" showZeroValue />
      </span>
    );
    const remainingBalance = remainingAmount ? (
      <span>
        {Drupal.t('Remaining Balance - ', {}, { context: 'egift' })}
        <PriceElement amount={remainingAmount} format="string" showZeroValue />
      </span>
    ) : '';

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
                  egiftHeading: appliedAmount,
                  egiftSubHeading: remainingBalance,
                })}
                {egiftFormElement({
                  type: 'number',
                  name: 'amount',
                  className: 'amount',
                  label: 'Amount',
                  value: getAmountWithCurrency(amount, false).amount,
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
