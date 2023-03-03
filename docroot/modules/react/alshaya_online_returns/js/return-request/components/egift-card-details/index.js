import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardTypeSVG from '../../../../../alshaya_spc/js/svg-component/card-type-svg';

const EgiftCardDetails = ({
  cardList, selectedOption,
}) => {
  let selected = cardList[0].card_number;
  if (hasValue(selectedOption)) {
    selected = selectedOption;
  }

  return (
    <>
      <div className="method-list-wrapper">
        {Object.keys(cardList).map((key) => (
          <div className="method-wrapper" key={`${cardList[key].card_number}`}>
            <input
              type="radio"
              value={`${cardList[key].card_number}`}
              name={`${cardList[key].card_number}`}
              checked={selected === `${cardList[key].card_number}`}
            />
            <label className="radio-sim radio-label">
              <CardTypeSVG type="egift" class={`${cardList[key].card_number} is-active`} />
              <div className="egift-card-detail">
                <span>
                  {Drupal.t('eGift Card', {}, { context: 'online_returns' })}
                </span>
              </div>
            </label>
          </div>
        ))}
      </div>
      <div className="refund-message">
        {Drupal.t('Your refund will be credited immediately after the item is returned to warehouse', {}, { context: 'online_returns' })}
      </div>
    </>
  );
};

export default EgiftCardDetails;
