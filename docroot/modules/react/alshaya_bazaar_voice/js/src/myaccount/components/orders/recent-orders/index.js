import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import { getbazaarVoiceSettings } from '../../../../utilities/api/request';
import WriteReviewButton from '../../../../reviews/components/reviews-full-submit';
import ViewReviewButton from '../view-review-button';

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
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    if (bazaarVoiceSettings.reviews.user.review !== null) {
      this.setState({
        rating: bazaarVoiceSettings.reviews.user.review.rating,
        reviewSummary: bazaarVoiceSettings.reviews.user.review.review_summary,
        productSummary: bazaarVoiceSettings.reviews.user.review.product_summary,
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
