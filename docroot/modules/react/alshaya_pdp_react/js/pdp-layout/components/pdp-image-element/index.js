import React from 'react';

const PdpImageElement = ({
  imageUrl, alt, title, index, onClick, children,
}) => {
  const openFullScreenView = (event) => {
    if (onClick) {
      onClick(event);
    }
  };
  return (
    <div className="magv2-pdp-image" onClick={openFullScreenView}>
      {children}
      <img
        src={imageUrl}
        alt={alt}
        title={title}
        data-index={index}
      />
    </div>
  );
};

export default PdpImageElement;
