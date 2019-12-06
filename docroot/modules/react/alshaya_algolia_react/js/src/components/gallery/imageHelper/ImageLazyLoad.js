import React, { Suspense, lazy } from 'react';
import ImageElement from './ImageElement';
// const ImageElement = lazy(() => import('./ImageElement'));

const ImageLazyLoad = (props) => {
  const {src, title, alt, ...otherProps} = props;

  if (src === '') {
    return (null);
  }

  return (
    // <Suspense fallback={<div>Loading...</div>}>
      <ImageElement
        src={src}
        alt={alt || title}
        title={title || ''}
        {...otherProps}
      />
    // </Suspense>
  );
};

export default ImageLazyLoad;
