import React from 'react';

const UserReviewsProducts = ({
  reviewsIndividualSummary,
  reviewsProduct,
}) => {
  if (reviewsIndividualSummary === null && reviewsProduct === null) {
    return null;
  }
  return (
    <div className="product-block">
      <div className="product-image-block">
        <img src={reviewsProduct[reviewsIndividualSummary.ProductId].ImageUrl} />
      </div>
      <div className="product-desc-block">
        <div className="product-title">
          <span>{reviewsProduct[reviewsIndividualSummary.ProductId].Name}</span>
        </div>
        <div className="product-item-code">
          {Drupal.t('Item Code')}
          {' '}
          :
          { reviewsProduct[reviewsIndividualSummary.ProductId].Id }
        </div>
      </div>
    </div>
  );
};

export default UserReviewsProducts;
