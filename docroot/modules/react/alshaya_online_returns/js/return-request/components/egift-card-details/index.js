import React from 'react';
import CardTypeSVG from '../../../../../alshaya_spc/js/svg-component/card-type-svg';

const EgiftCardDetails = ({ cardList }) => (
  <>
    <div className="method-list-wrapper">
      {Object.keys(cardList).map((key) => (
        <div className="method-wrapper">
          <input type="radio" value={`${cardList[key].card_number}`} name={`${cardList[key].email}`} />
          <div className="card-icon">
            <CardTypeSVG type="egift" class={`${cardList[key].card_number} is-active`} />
          </div>
          <div className="egift-card-detail">
            <span>
              { Drupal.t('eGift Card', {}, { context: 'online_returns' }) }
            </span>
          </div>
        </div>
      ))}
    </div>
    <div className="refund-message">
      { Drupal.t('Your refund will be credited immediately after the item is returned to warehouse', {}, { context: 'online_returns' }) }
    </div>
  </>
);

export default EgiftCardDetails;
