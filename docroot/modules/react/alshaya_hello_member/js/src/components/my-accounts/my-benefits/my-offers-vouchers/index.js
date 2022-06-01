import React, { useState } from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';

const MyOffersAndVouchers = ({ myBenefitsList }) => {
  const showMoreLimit = 3;
  const [expanded, setExpanded] = useState(false);
  const dataForDisplay = expanded ? myBenefitsList : myBenefitsList.slice(0, showMoreLimit);

  return (
    <div className="my-benefits-wrapper">
      {dataForDisplay.map((data) => (
        <div className="my-offers-vouchers-details" key={data.id}>
          <div className="image-container">
            <img src={data.image} />
          </div>
          <div className="voucher-wrapper">
            <div className="title">
              {data.name}
            </div>
            <div className="info">
              {data.description}
            </div>
            <div className="expiry">
              {data.end_date}
            </div>
          </div>
        </div>
      ))}
      <ConditionalView condition={myBenefitsList.length > showMoreLimit}>
        <div className="btn-wrapper">
          <a onClick={() => setExpanded(!expanded)}>
            {expanded ? getStringMessage('show_less') : getStringMessage('show_all')}
          </a>
        </div>
      </ConditionalView>
    </div>
  );
};

export default MyOffersAndVouchers;
