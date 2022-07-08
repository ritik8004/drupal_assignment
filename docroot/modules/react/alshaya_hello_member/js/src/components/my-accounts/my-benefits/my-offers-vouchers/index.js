import React, { useState } from 'react';
import moment from 'moment';
import ConditionalView from '../../../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { isMobile } from '../../../../../../../js/utilities/display';

const MyOffersAndVouchers = ({ myBenefitsList }) => {
  const showMoreLimit = 3;
  const display = isMobile();
  const [expanded, setExpanded] = useState(display);
  const dataForDisplay = expanded ? myBenefitsList : myBenefitsList.slice(0, showMoreLimit);
  const { currentPath } = drupalSettings.path;

  return (
    <div className="my-benefits-wrapper">
      {dataForDisplay.map((data) => (
        <a className="my-offers-vouchers-details" key={data.id || data.code} href={`${Drupal.url(currentPath)}/hello-member-benefits/${hasValue(data.id) ? `coupon/${data.id}` : `offer/${data.code}`}`}>
          <div className="image-container">
            <img src={data.small_image} />
          </div>
          <div className="voucher-wrapper">
            <div className="title">
              {data.category_name}
            </div>
            <div className="info">
              {data.description}
            </div>
            <div className="expiry">
              {getStringMessage('benefit_expire', { '@expire_date': moment(new Date(data.expiry_date || data.end_date)).format('DD MMMM YYYY') })}
            </div>
          </div>
        </a>
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
