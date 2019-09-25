import React from 'react';

const ImageElement = ({src, title}) => {
  return (
    <img
      src={src}
      alt={title}
      title={title}
      className="b-lazy b-loaded"
    />
  );
};

export default ImageElement;