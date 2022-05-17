import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const DeliveryDetailNotice = (props) => {
  const { order } = props;

  return (
    <>
      <ConditionalView condition={hasValue(order.delivery_detail_notice)}>
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
      </ConditionalView>
    </>
  );
};

export default DeliveryDetailNotice;
