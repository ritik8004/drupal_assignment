import React from 'react';

const DeviceView = ({ device, children }) => {
  // Return null, if display is not Mobile.
  if (device === 'mobile' && window.innerWidth >= 768) {
    return (null);
  }

  // Return null, if display is above-mobile and current display with
  // is of mobile.
  if (device === 'above-modile' && window.innerWidth < 768) {
    return (null);
  }

  return (
    <>
      {children}
    </>
  );
};

export default DeviceView;
