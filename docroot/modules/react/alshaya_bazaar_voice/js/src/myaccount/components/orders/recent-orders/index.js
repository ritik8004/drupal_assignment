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
      userDetails: {
        productReview: null,
      },
    };
  }

  componentDidMount() {
    const { productId } = this.props;
    getUserDetails(productId).then((result) => {
      this.setState({ userDetails: result }, () => {
        const { userDetails } = this.state;
        if (userDetails.productReview !== null && userDetails.productReview !== 0) {
          this.setState({
            rating: userDetails.productReview.user_rating,
            reviewData: userDetails.productReview.review_data,
          });
        }
      });
    });
  }

  render() {
    const { productId } = this.props;
    const {
      rating,
      reviewData,
      userDetails,
    } = this.state;

    if (userDetails.productReview === null) {
      return null;
    }

    return (
      <>
        <ConditionalView condition={reviewData === ''}>
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
