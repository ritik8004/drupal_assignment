import React from 'react';

const Labels = ({ labels, sku }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }
  const labelItems = labels.map(({ image, position, text }) => (
    <div className={`label ${position}`} key={image}>
      <img
        src={image}
        alt={text || ''}
        title={text || ''}
      />
    </div>
  ));

  return (
    <div className="labels-container" data-type="spc-recommended-products" data-sku={sku} data-main-sku={sku}>
      {labelItems}
    </div>
  );
};

export default Labels;
