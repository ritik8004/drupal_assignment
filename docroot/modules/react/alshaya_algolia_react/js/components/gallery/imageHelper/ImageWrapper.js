import React from 'react';
import ImageElement from './ImageElement';

export function ImageWrapper(imageObj, title, classname, showDefaultImage = false) {
  let imageTag = '';
  if (typeof imageObj != 'undefined' && typeof imageObj.url != 'undefined') {
    imageTag = <ImageElement src={ imageObj.url } title={title} />;
  }
  else if (showDefaultImage && drupalSettings.reactTeaserView.gallery.defaultImage) {
    imageTag = <ImageElement src={ drupalSettings.reactTeaserView.gallery.defaultImage } title={title} />;
  }

  return (<div className={classname}>{imageTag}</div>);
};
