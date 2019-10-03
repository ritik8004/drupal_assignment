import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';

const Gallery = (props) => {
  if (drupalSettings.reactTeaserView.gallery.showHoverImage) {
    return (<AssetGallery {...props}/>);
  }
  else {
    return (<SearchGallery {...props}/>);
  }
};

export default Gallery;
