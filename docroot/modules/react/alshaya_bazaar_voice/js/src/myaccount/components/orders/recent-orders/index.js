import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import { getUserDetails } from '../../../../utilities/api/request';
import WriteReviewButton from '../../../../reviews/components/reviews-full-submit';
import ViewReviewButton from '../view-review-button';

export default class RecentOrders extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      rating: '',
      reviewData: '',
      userDetails: null,
    };
  }

  componentDidMount() {
    const { productId } = this.props;
    getUserDetails(productId).then((userDetails) => {
      if (userDetails && Object.keys(userDetails).length !== 0
        && userDetails.productReview !== null) {
        this.setState({
          userDetails,
          rating: userDetails.productReview.user_rating,
          reviewData: userDetails.productReview.review_data,
        });
      }
    });
  }

  render() {
    const { productId } = this.props;
    const {
      rating,
      reviewData,
      userDetails,
    } = this.state;

    if (userDetails && Object.keys(userDetails).length === 0) {
      return null;
    }

    return (
      <>
        <ConditionalView condition={reviewData === '' && productId !== ''}>
          <WriteReviewButton
            reviewedByCurrentUser={false}
            productId={productId}
            context="myaccount"
          />
        </ConditionalView>
        <ConditionalView condition={reviewData !== ''}>
          <ViewReviewButton
            rating={rating}
            reviewData={reviewData}
          />
        </ConditionalView>
      </>
    );
  }
}
