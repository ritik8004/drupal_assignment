import React from 'react';

const Promotion = (props) => {
  return (
    <span className="sku-promotion-item">
      <a className="sku-promotion-link" href="{{ path('entity.node.canonical', {'node': id }) }}">
        {props.promotion.text}
      </a>
    </span>
  );
};

export default Promotion;