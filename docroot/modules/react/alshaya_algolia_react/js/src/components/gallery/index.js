import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import SingleImageGallery from './variants/SingleImageGallery';
import { isMobile } from '../../utils';

const Gallery = (props) => {
  const { media } = props;
  if (typeof media === 'undefined') {
    return (null);
  }

  if (isMobile()) {
    return (<SearchGallery {...props} />);
  }

  if (drupalSettings.reactTeaserView.gallery.showHoverImage) {
    return (<AssetGallery {...props} />);
  }
  if (drupalSettings.reactTeaserView.gallery.showThumbnails) {
    return (<SearchGallery {...props} />);
  }

  return (<SingleImageGallery {...props} />);
};

export default Gallery;
