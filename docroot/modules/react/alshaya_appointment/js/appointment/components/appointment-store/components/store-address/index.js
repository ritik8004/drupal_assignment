import React from 'react';

const StoreAddress = (address) => (
  <div className="address-wrapper">
    {address.address && Object.entries(address.address).map(([i, value]) => {
      // Removing not avaialable string (N/A) and countryCode from address.
      if (value && value !== '(N/A)' && i !== 'countryCode') {
        return (
          <>
            {value && i !== 'address1' && ', '}
            {value}
          </>
        );
      }
    })}
  </div>
);

export default StoreAddress;
