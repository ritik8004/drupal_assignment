import React from 'react';
import ImageElement from './ImageElement';

export function ImageWrapper(imageObj, title, classname, showDefaultImage = false) {
  if (typeof imageObj != 'undefined' && typeof imageObj.url != 'undefined') {
    return (
      <div className={classname}>
        <ImageElement src={ imageObj.url } title={title} />
      </div>
    );
  }

  if (showDefaultImage && drupalSettings.reactTeaserView.gallery.defaultImage) {
    return (
      <div className={classname}>
        <ImageElement src={ drupalSettings.reactTeaserView.gallery.defaultImage } title={title} />
      </div>
    );
  }

  return '';
};
