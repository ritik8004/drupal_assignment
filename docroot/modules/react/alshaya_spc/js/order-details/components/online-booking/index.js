import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OnlineBooking = (props) => {
  const { order } = props;
  if (!hasValue(order.online_booking_notice)) {
    return null;
  }
  const { notice } = order.online_booking_notice;

  return (
    <div className="online-booking-details-row">
      <div>
        <span className="icon-ic_infomation" />
        <span className="online-booking-details">
          <ConditionalView condition={hasValue(notice.booking_info)}>
            <span className="booking-info">{notice.booking_info}</span>
          </ConditionalView>
          <ConditionalView condition={hasValue(notice.customer_care_info)}>
            <span className="customer-care-info">{notice.customer_care_info}</span>
          </ConditionalView>
          <ConditionalView condition={hasValue(notice.update_booking_info)}>
            <span className="update-booking-info">{notice.update_booking_info}</span>
          </ConditionalView>
          <ConditionalView condition={hasValue(notice.booking_error)}>
            <span className="booking-error">{notice.booking_error}</span>
          </ConditionalView>
        </span>
      </div>
    </div>
  );
};

export default OnlineBooking;
