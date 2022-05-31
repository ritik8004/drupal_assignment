import React from 'react';

const PdpImageElement = ({
  imageUrl, alt, title, index, onClick, children, miniFullScreenGallery,
}) => {
  const openFullScreenView = (event) => {
    if (onClick) {
      onClick(event);
    }
  };

  return (
    // eslint-disable-next-line react/jsx-props-no-spreading
    <div className="magv2-pdp-image" {...(miniFullScreenGallery && { onClick: openFullScreenView })}>
      {children}
      <img
        src={imageUrl}
        alt={alt}
        title={title}
        data-index={index}
        loading={index === 0 ? 'eager' : 'lazy'}
      />
    </div>
  );
};

export default PdpImageElement;
