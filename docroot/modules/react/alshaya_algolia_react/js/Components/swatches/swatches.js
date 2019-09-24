import React from 'react';

const Swatches = (props) => {
  const dataSkuImage =  (props.swatch.product_url) ? `data-sku-image=${props.swatch.product_url}` : '';

  return (
    <a href=`${props.url}?selected=${props.key}`>
      <span className="swatch-block swatch-image">
        <img {dataSkuImage} src={props.swatch.url} />
      </span>
    </a>
  );

export default Swatches;
