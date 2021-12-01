import React from 'react';
import { getMdcMediaUrl } from '../../../utilities';

const HeroImage = (props) => {
  const { item } = props;
  const media = item.media_gallery_entries;
  const image = {
    url: (media.length > 0) ? `${getMdcMediaUrl()}${media[0].file}` : '',
    title: item.name,
    alt: item.name,
  };
  const style = {
    width: '50%',
  };

  return (
    <div className="hero-image-wrapper" style={style}>
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
