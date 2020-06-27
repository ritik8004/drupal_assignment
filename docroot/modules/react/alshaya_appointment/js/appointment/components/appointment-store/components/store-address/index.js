import React from 'react';

const StoreAddress = (address) => (
  <div className="address-wrapper">
    {address.address && Object.entries(address.address).map(([i, value]) => (
      <>
        {value && i !== 'address1' && ', '}
        {value}
      </>
    ))}
  </div>
);

export default StoreAddress;
