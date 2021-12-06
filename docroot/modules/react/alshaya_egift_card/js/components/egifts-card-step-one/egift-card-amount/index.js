import React from 'react';
import EgiftCardOpenAmountField from '../egift-card-open-amount-field';
import getCurrencyCode from '../../../../../js/utilities/util';

/**
 * Show list of egift card selectable amounts from api.
 */
const EgiftCardAmount = (props) => {
  const { selected, handleAmountSelect } = props;

  // Get amounts that user can select from api response items.
  const amounts = selected.extension_attributes.hps_giftcard_amount;

  // List all amounts.
  const listItems = amounts.map((amount) => (<li onClick={() => handleAmountSelect(true, amount.value)} key={amount.value}>{amount.value}</li>));

  return (
    <div className="egift-card-amounts">
      <p>
        {
          Drupal.t('Amount @currencyCode', {
            '@currencyCode': getCurrencyCode(),
          }, { context: 'egift' })
        }
      </p>
      <ul>
        {listItems}
      </ul>
      <EgiftCardOpenAmountField selected={selected} />
    </div>
  );
};

export default EgiftCardAmount;
