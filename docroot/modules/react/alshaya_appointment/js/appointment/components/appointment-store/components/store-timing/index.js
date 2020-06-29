import React from 'react';

const StoreTiming = (timing) => (
  <div className="store-timing-wrapper">
    <h5>
      {Drupal.t('Store Timings')}
    </h5>
    {timing.timing && Object.entries(timing.timing).map(([k, value]) => (
      <div>
        {`${k} (${value})`}
      </div>
    ))}
  </div>
);

export default StoreTiming;
