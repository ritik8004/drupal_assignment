import React from 'react';
import ImageLazyLoad from './ImageLazyLoad';

export function ImageWrapper({src, title, className, showDefaultImage = false, ...otherProps}) {
  let imageSrc = '';
  if (typeof src != 'undefined' && src !== '') {
    imageSrc = src;
  }
  else if (showDefaultImage && drupalSettings.reactTeaserView.gallery.defaultImage) {
    imageSrc = drupalSettings.reactTeaserView.gallery.defaultImage;
  }

  return (
    <div className={className}>
      <ImageLazyLoad src={imageSrc} title={title} {...otherProps} />
    </div>
  );
};
