import React from 'react';
import ImageElement from './ImageElement';
import ImageLazyLoad from './ImageLazyLoad';

export function ImageWrapper(imageObj, title, classname, showDefaultImage = false) {
  let imageTag = '';
  if (typeof imageObj != 'undefined' && typeof imageObj.url != 'undefined') {
    imageTag = <ImageLazyLoad src={ imageObj.url } title={title} />;
  }
  else if (showDefaultImage && drupalSettings.reactTeaserView.gallery.defaultImage) {
    imageTag = <ImageLazyLoad src={ drupalSettings.reactTeaserView.gallery.defaultImage } title={title} />;
  }

  return (<div className={classname}>{imageTag}</div>);
};
