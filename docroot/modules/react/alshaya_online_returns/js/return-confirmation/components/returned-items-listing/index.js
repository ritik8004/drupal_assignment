import React, { useEffect } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';
import { getPreparedOrderGtm, getProductGtmInfo } from '../../../utilities/online_returns_gtm_util';
import { getReturnedItems } from '../../../utilities/return_confirmation_util';

const ReturnedItemsListing = ({
  returnData,
}) => {
  const returnedItems = getReturnedItems(returnData);
  if (!hasValue(returnedItems)) {
    return null;
  }

  useEffect(() => {
    // Push the required info to GTM.
    const triggerGtmForReturnConfirmation = async () => {
      Drupal.alshayaSeoGtmPushReturn(
        getProductGtmInfo(returnedItems),
        await getPreparedOrderGtm('returnconfirmed', returnData),
        'returnconfirmed',
      );
    };

    // Call the function.
    triggerGtmForReturnConfirmation();
  });

  return (
    <div className="return-items-wrapper">
      <div className="return-items-label">
        <div className="return-items-title">{ Drupal.t('Items to return', {}, { context: 'online_returns' }) }</div>
      </div>
      {returnedItems.map((item) => (
        <div key={item.sku} className="item-details">
          <ReturnIndividualItem item={item} />
        </div>
      ))}
    </div>
  );
};

export default ReturnedItemsListing;
