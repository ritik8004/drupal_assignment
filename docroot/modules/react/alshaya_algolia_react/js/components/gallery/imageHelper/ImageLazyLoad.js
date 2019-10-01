import React, { Suspense, lazy } from 'react';

const ImageElement = lazy(() => import('./ImageElement'));

const ImageLazyLoad = (props) => {
  const {src, title, alt, ...otherProps} = props;

  return (
    <div>
      <Suspense fallback={<div>Loading...</div>}>
        <ImageElement
          src={src}
          alt={alt || title}
          title={title || ''}
          {...otherProps}
        />
      </Suspense>
    </div>
  );
};

export default ImageLazyLoad;
