import React from 'react';
import { getMdcMediaUrl } from '../../../utilities';

const EgiftCardThumbnail = (props) => {
  const { item, selected, handleEgiftSelect } = props;
  const media = item.media_gallery_entries;
  const thumbnailImage = {
    url: (media.length > 0) ? `${getMdcMediaUrl()}${media[0].file}` : '',
    title: item.name,
    alt: item.name,
  };

  let classList = 'card-thumbnail-image';
  if (selected.id === item.id) {
    classList = `${classList} active`;
  }

  return (
    <li onClick={() => handleEgiftSelect(item.id)}>
      <img
        src={thumbnailImage.url}
        alt={thumbnailImage.alt}
        title={thumbnailImage.title}
        className={classList}
      />
    </li>
  );
};

export default EgiftCardThumbnail;
