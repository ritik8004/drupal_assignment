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
      reviewData: '',
    };
  }

  componentDidMount() {
    const { productId } = this.props;
    const userDetails = getUserDetails(productId);
    createUserStorage(userDetails.user.userId, userDetails.user.emailId);
    if (userDetails.productReview !== null) {
      this.setState({
        rating: userDetails.productReview.rating,
        reviewData: userDetails.productReview.review_data,
      });
    }
  }

  render() {
    const { productId } = this.props;
    const {
      rating, reviewData,
    } = this.state;
    return (
      <>
        <ConditionalView condition={reviewData === ''}>
          <div className="button-wrapper">
            <WriteReviewButton
              reviewedByCurrentUser={false}
              productId={productId}
              context="myaccount"
            />
          </div>
        </ConditionalView>
        <ConditionalView condition={reviewData !== ''}>
          <div className="button-wrapper">
            <ViewReviewButton
              rating={rating}
              reviewData={reviewData}
            />
          </div>
        </ConditionalView>
      </>
    );
  }
}
