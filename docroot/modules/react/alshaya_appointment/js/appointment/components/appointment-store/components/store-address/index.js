import React from 'react';
import { addressCleanup } from '../../../../../utilities/helper';

const StoreAddress = (address) => (
  <div className="address-wrapper">
    {addressCleanup(address.address)}
  </div>
);

export default StoreAddress;
