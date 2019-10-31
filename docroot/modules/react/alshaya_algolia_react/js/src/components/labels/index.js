import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const Labels = ({labels, sku}) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }
  const labelItems = labels.map(({image, position}) => (
    <div className={`label ${position}`} key={image.url}>
      <ImageElement src={image.url} alt={image.alt} title={image.title} />
    </div>
  ));

  return (
    <div className="labels-container" data-type="plp" data-sku={sku} data-main-sku={sku}>
      {labelItems}
    </div>
  );
};

export default Labels;

