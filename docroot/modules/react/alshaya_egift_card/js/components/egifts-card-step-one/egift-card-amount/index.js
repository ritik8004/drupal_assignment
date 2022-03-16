import React from 'react';
import EgiftCardOpenAmountField from '../egift-card-open-amount-field';
import getCurrencyCode from '../../../../../js/utilities/util';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';

/**
 * Show list of egift card selectable amounts from api.
 */
const EgiftCardAmount = (props) => {
  const {
    selected, handleAmountSelect, myAccountLabel, field,
  } = props;

  const labelOption = typeof myAccountLabel !== 'undefined' ? myAccountLabel : false;

  // Get amounts that user can select from api response items.
  const amounts = selected.extension_attributes.hps_giftcard_amount;

  const handleAmount = (e, amount) => {
    // Remove any existing active class.
    const amountElements = document.querySelectorAll('.item-amount');
    [].forEach.call(amountElements, (el) => {
      el.classList.remove('active');
    });

    // Empty open amount field and unlock
    const openAmountInput = (field.current !== null) ? field.current.querySelector('input') : null;
    const openAmountButton = (field.current !== null) ? field.current.querySelector('button') : null;
    if (openAmountInput !== null) {
      openAmountInput.value = '';
      openAmountInput.removeAttribute('readOnly');
      // Remove any error message from open amount.
      document.getElementById('open-amount-error').innerHTML = '';
      openAmountButton.disabled = true;
    }

    // Set target as element as active.
    const element = e.target;
    element.classList.add('active');

    // Set amount for step 2.
    handleAmountSelect(true, amount);
  };

  // List all amounts.
  const listItems = amounts.map((amount) => (
    <li
      key={amount.value}
      className="item-amount"
      onClick={(e) => handleAmount(e, amount.value)}
    >
      {amount.value}
    </li>
  ));

  return (
    <div className="egift-card-amount-list-wrapper">
      <ConditionalView condition={labelOption === false}>
        <div className="egift-card-amount-list-title subtitle-text">
          {
            Drupal.t('Amount @currencyCode', {
              '@currencyCode': getCurrencyCode(),
            }, { context: 'egift' })
          }
        </div>
      </ConditionalView>
      <ConditionalView condition={labelOption}>
        <div className="egift-card-amount-list-title subtitle-text">
          {
            Drupal.t('Choose Top up Amount (@currencyCode)', {
              '@currencyCode': getCurrencyCode(),
            }, { context: 'egift' })
          }
        </div>
      </ConditionalView>
      <ul>
        {listItems}
      </ul>
      <EgiftCardOpenAmountField
        selected={selected}
        handleAmountSelect={handleAmountSelect}
        field={field}
      />
    </div>
  );
};

export default EgiftCardAmount;
