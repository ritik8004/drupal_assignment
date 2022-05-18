import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const DeliveryDetailNotice = (props) => {
  const { order } = props;
  if (!hasValue(order.delivery_detail_notice)) {
    return null;
  }

  return (
    <>
      <div className="delivery-details-row">
        <div className="above-mobile">
          <span className="icon-ic_infomation" />
          {order.delivery_detail_notice}
        </div>
        <div className="mobile-only">
          <span className="icon-ic_infomation" />
          {order.delivery_detail_notice}
        </div>
      </div>
    </>
  );
};

export default DeliveryDetailNotice;
