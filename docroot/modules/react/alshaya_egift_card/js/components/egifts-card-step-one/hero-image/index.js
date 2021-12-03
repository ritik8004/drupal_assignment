import React from 'react';
import { getMdcMediaUrl } from '../../../utilities';

/**
 * Show egift card hero image from selected item.
 */
const HeroImage = (props) => {
  const { item } = props;
  const media = item.media_gallery_entries;
  // @todo handle cards without image.
  const image = {
    url: (media.length > 0) ? `${getMdcMediaUrl()}${media[0].file}` : '',
    title: item.name,
    alt: item.name,
  };

  return (
    <div className="hero-image-wrapper" style={{ width: '50%' }}>
      <img
        src={image.url}
        alt={image.alt}
        title={image.title}
        className="hero-image"
      />
    </div>
  );
};

export default HeroImage;
