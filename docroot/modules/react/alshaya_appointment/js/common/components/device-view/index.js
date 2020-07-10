import React from 'react';

const DeviceView = ({ device, children }) => {
  // Return null, if display is not Mobile.
  if (device === 'mobile' && window.innerWidth >= 768) {
    return (null);
  }

  // Return null, if device is "above-mobile" and current display
  // width is of mobile.
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
