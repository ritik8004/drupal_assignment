import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import SingleImageGallery from './variants/SingleImageGallery';
import { isMobile } from '../../utils';

const Gallery = (props) => {
  if (typeof props.media === 'undefined') {
    return (null);
  }

  if (isMobile()) {
    return (<SingleImageGallery {...props}/>);
  }
  else {
    if (drupalSettings.reactTeaserView.gallery.showHoverImage) {
      return (<AssetGallery {...props}/>);
    }
    else if (drupalSettings.reactTeaserView.gallery.showThumbnails) {
      return (<SearchGallery {...props}/>);
    }
    else {
      return (<SingleImageGallery {...props} />);
    }
  }

};

export default Gallery;
