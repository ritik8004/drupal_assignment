import React from 'react';
import Popup from 'reactjs-popup';
import DisplayStar from '../../../../rating/components/stars';
import getStringMessage from '../../../../../../../js/utilities/strings';
import ViewReviewPopup from '../view-review-popup';
import SectionTitle from '../../../../utilities/section-title';

export default class ViewReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  openModal = (e) => {
    e.preventDefault();
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = (e) => {
    e.preventDefault();
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  render() {
    const { isModelOpen } = this.state;
    const {
      rating, reviewSummary, productSummary,
    } = this.props;
    return (
      <>
        <DisplayStar
          starPercentage={rating}
        />
        <div onClick={(e) => this.openModal(e)} className="view-review-button">
          {getStringMessage('view_review')}
        </div>
        <Popup
          open={isModelOpen}
          className="view_review"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="write-review-form">
            <div className="title-block">
              <SectionTitle>{getStringMessage('view_review')}</SectionTitle>
              <a className="close-modal" onClick={(e) => this.closeModal(e)} />
            </div>
            <ViewReviewPopup
              reviewData={reviewSummary}
              productData={productSummary}
            />
          </div>
        </Popup>
      </>
    );
  }
}
