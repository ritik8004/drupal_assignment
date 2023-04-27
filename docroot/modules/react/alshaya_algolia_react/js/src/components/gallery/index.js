import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import SingleImageGallery from './variants/SingleImageGallery';
import { isDesktop } from '../../../../../js/utilities/display';

const Gallery = (props) => {
  const { media } = props;
  if (typeof media === 'undefined') {
    return (null);
  }

  if (!isDesktop() && drupalSettings.reactTeaserView.swipe_image.enable_swipe_image_mobile) {
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
