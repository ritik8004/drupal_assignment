import React from 'react';

const ImageElement = (props) => {
  const {
    src, title, alt, ...otherProps
  } = props;

  const parsedTitle = title.replace(/<\/?[^>]+(>|$)/g, ' ');

  return (
    <img
      src={src}
      alt={alt || parsedTitle}
      title={parsedTitle || ''}
      {...otherProps}
    />
  );
};

export default ImageElement;
