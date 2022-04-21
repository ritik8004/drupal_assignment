import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';
import { getReturnedItems } from '../../../utilities/return_confirmation_util';

const ReturnedItemsListing = ({
  returnData,
}) => {
  const returnedItems = getReturnedItems(returnData);
  if (!hasValue(returnedItems)) {
    return null;
  }

  return (
    <div className="return-items-wrapper">
      {returnedItems.map((item) => (
        <div key={item.sku} className="item-details">
          <ReturnIndividualItem
            item={item}
          />
        </div>
      ))}
    </div>
  );
};

export default ReturnedItemsListing;
