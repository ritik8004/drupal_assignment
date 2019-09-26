import React from 'react';

const ImageElement = ({src, title, alt}) => {
  return (
    <img
      src={src}
      alt={alt || title}
      title={title || ''}
      className="b-lazy b-loaded"
    />
  );
};

export default ImageElement;