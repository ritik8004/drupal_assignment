import React from 'react';

import RecommendedProduct from '../../../utilities/recommended-product';

export default class CartRecommendedProducts extends React.Component {

  render() {
    const recommended_products = this.props.recommended_products;

    // If recommended products available.
    if (Object.keys(recommended_products).length > 0) {
      return Object.keys(recommended_products).map(function(key) {
        return <RecommendedProduct key={key} item={recommended_products[key]}/>
      });
    }

    return (null);
  }

}
