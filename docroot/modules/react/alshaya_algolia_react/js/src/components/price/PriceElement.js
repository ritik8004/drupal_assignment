import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

/**
 * Render max amount element.
 */
const MaxPriceItem = ({ maxAmount }) => {
  if (!hasValue(maxAmount) || maxAmount === 0) {
    return (null);
  }
  const { decimalPoints } = drupalSettings.reactTeaserView.price;
  return (
    <>
      <span className="min-max-separator">-</span>
      {Number(maxAmount).toFixed(decimalPoints)}
    </>
  );
};

/**
 * Render price item element.
 */
const PriceItem = ({ amount, maxAmount }) => {
  const { decimalPoints } = drupalSettings.reactTeaserView.price;
  if ((!hasValue(amount) || amount === 0)
    && (hasValue(maxAmount) || maxAmount !== 0)
  ) {
    return (
      <span key="amount" className="price-amount">
        {Number(maxAmount).toFixed(decimalPoints)}
      </span>
    );
  }

  return (
    <span key="amount" className="price-amount">
      {Number(amount).toFixed(decimalPoints)}
      <MaxPriceItem maxAmount={maxAmount} />
    </span>
  );
};

const PriceElement = ({ amount, maxAmount, fixedPrice = '' }) => {
  if (typeof amount === 'undefined') {
    return (null);
  }

  const { currency } = drupalSettings.reactTeaserView.price;

  const priceParts = [
    (
      <span key="currency" className="price-currency suffix">{currency}</span>
    ),
    (<PriceItem key="price-item" amount={amount} maxAmount={maxAmount} />),
  ];

  return (
    <span className="price-wrapper">
      <div className={`price ${hasValue(currency) ? currency.toLowerCase() : ''}`} data-fp={fixedPrice}>
        {drupalSettings.reactTeaserView.price.currencyPosition === 'before' ? priceParts : priceParts.reverse()}
      </div>
    </span>
  );
};

export default PriceElement;
