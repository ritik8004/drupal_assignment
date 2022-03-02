import React from 'react';
import { getImageUrl } from '../../../utilities';

const EgiftCardThumbnail = (props) => {
  const { item, selected, handleEgiftSelect } = props;

  const { custom_attributes: customAttributes } = item || [];

  const thumbnailImage = {
    url: getImageUrl(customAttributes, 'thumbnail'),
    title: item.name,
    alt: item.name,
  };

  let classList = 'card-thumbnail-image';
  if (selected.id === item.id) {
    classList = `${classList} active`;
  }

  return (
    <li className={classList} onClick={() => handleEgiftSelect(item.id)}>
      <img
        src={thumbnailImage.url}
        alt={thumbnailImage.alt}
        title={thumbnailImage.title}
      />
    </li>
  );
};

export default EgiftCardThumbnail;
