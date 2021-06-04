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
    createUserStorage(userDetails.user.webUserID, userDetails.user.userEmailID);
    if (userDetails.user.productReview !== null) {
      this.setState({
        rating: userDetails.user.productReview.rating,
        reviewSummary: userDetails.user.productReview.review_summary,
        productSummary: userDetails.user.productReview.product_summary,
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
