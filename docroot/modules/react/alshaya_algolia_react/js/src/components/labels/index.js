import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const Labels = ({ labels, sku }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }

  const labelItems = labels.map(({ image }) => (
    <div className="label" key={image.url}>
      <ImageElement src={image.url} alt={image.alt} title={image.title} />
    </div>
  ));

  const labelPosition = (labels.length) ? labels[0].position : '';

  return (
    <div className={`labels-container ${labelPosition}`} data-type="plp" data-sku={sku} data-main-sku={sku}>
      {labelItems}
    </div>
  );
};

export default Labels;
