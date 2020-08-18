import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';

const StoreTiming = (timing) => (
  <div className="store-delivery-time">
    <span className="label--delivery-time">
      {getStringMessage('store_timing_label')}
    </span>
    {timing.timing && Object.entries(timing.timing).map(([, value]) => (
      <span className="delivery--time--value" key={value.timeSlot}>
        {`${value.day} (${value.timeSlot})`}
      </span>
    ))}
  </div>
);

export default StoreTiming;
