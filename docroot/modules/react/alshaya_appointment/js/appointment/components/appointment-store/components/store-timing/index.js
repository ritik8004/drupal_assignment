import React from 'react';

const StoreTiming = (timing) => (
  <div className="store-delivery-time">
    <span className="label--delivery-time">
      {Drupal.t('Store Timings')}
    </span>
    {timing.timing && Object.entries(timing.timing).map(([, value]) => (
      <span className="delivery--time--value">
        {`${value.day} (${value.timeSlot})`}
      </span>
    ))}
  </div>
);

export default StoreTiming;
