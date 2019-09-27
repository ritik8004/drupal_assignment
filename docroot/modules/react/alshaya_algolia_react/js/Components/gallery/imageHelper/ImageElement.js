import React from 'react';

const ImageElement = (props) => {
  const {src, title, alt, ...otherProps} = props;

  return (
    <img
      src={src}
      alt={alt || title}
      title={title || ''}
      {...otherProps}
    />
  );
};

export default ImageElement;