import React from 'react'

const DeviceView = ({device, children}) => {
  // Mobile only display
  if  (device === 'mobile' && window.innerWidth >= 768) {
    return (null);
  }

  // Tablet and desktop view only display
  if  (device === '!mobile' && window.innerWidth < 768) {
    return (null);
  }

  return (
    <React.Fragment>
      {children}
    </React.Fragment>
  )
}

export default DeviceView;
