import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import { getUserDetails } from '../../../../utilities/api/request';
import WriteReviewButton from '../../../../reviews/components/reviews-full-submit';
import ViewReviewButton from '../view-review-button';
import { createUserStorage } from '../../../../utilities/user_util';

export default class RecentOrders extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      rating: '',
      reviewSummary: '',
      productSummary: '',
    };
  }

  componentDidMount() {
    const { productId } = this.props;
    const userDetails = getUserDetails(productId);
    createUserStorage(userDetails.id, userDetails.email);
    if (userDetails.review !== null) {
      this.setState({
        rating: userDetails.review.rating,
        reviewSummary: userDetails.review.review_summary,
        productSummary: userDetails.review.product_summary,
      });
    }
  }

  render() {
    const { productId } = this.props;
    const {
      rating, reviewSummary, productSummary,
    } = this.state;
    return (
      <>
        <ConditionalView condition={reviewSummary === ''}>
          <div className="button-wrapper">
            <WriteReviewButton
              reviewedByCurrentUser={false}
              productId={productId}
              context="myaccount"
            />
          </div>
        </ConditionalView>
        <ConditionalView condition={reviewSummary !== ''}>
          <div className="button-wrapper">
            <ViewReviewButton
              rating={rating}
              reviewSummary={reviewSummary}
              productSummary={productSummary}
            />
          </div>
        </ConditionalView>
      </>
    );
  }
}
