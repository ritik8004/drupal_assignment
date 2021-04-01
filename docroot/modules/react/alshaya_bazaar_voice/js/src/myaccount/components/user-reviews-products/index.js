import React from 'react';

export default class UserReviewsProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const {
      reviewsIndividualSummary,
      reviewsProduct,
    } = this.props;
    return (
      <div className="product-block">
        <div className="product-image-block">
          <img src={reviewsProduct[reviewsIndividualSummary.ProductId].ImageUrl} />
        </div>
        <div className="product-title">
          <a href={reviewsProduct[reviewsIndividualSummary.ProductId].ProductPageUrl}>
            <span>{reviewsProduct[reviewsIndividualSummary.ProductId].Name}</span>
          </a>
        </div>
        <div className="product-item-code">
          {Drupal.t('Item Code')}
          {' '}
          :
          { reviewsProduct[reviewsIndividualSummary.ProductId].Id }
        </div>
      </div>
    );
  }
}
