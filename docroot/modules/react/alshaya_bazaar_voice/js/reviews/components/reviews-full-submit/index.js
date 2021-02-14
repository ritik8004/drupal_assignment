import React from 'react';
import Popup from 'reactjs-popup';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../alshaya_spc/js/utilities/checkout_util';
import { getFormConfig } from '../../../utilities/api/formData';
import Loading from '../../../utilities/loading';
import WithModal from './with-modal';
import WriteReviewForm from './WriteReviewForm';

export default class WriteReview extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      formFieldConfigs: '',
    };
  }

  /**
   * Get form fields from bazaarVoice.
   */
  componentDidMount() {
    showFullScreenLoader();
    const apiUri = '/bv-form-config';
    const apiData = getFormConfig(apiUri);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.status === 200 && result.statusText === 'OK') {
          removeFullScreenLoader();
          this.setState({
            formFieldConfigs: result.data,
          });
        } else {
          // Todo
        }
      });
    }
  }

  openModal = (callback) => {
    callback();
    // console.log('OpenModel');
  }

  closeModal = (callback) => {
    callback();
    // console.log('CloseModel');
  };

  // eventListener = (e) => {
  //   this.eventClosePopup();
  //   console.log('eventListener');
  // };
  //
  // eventClosePopup = () => {
  //   console.log('eventClosePopup');
  // };

  render() {
    const {
      formFieldConfigs,
    } = this.state;
    return (
      <WithModal>
        {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
          <div className="pdp-write-review">
            <div onClick={() => this.openModal(triggerOpenModal)} className="pdp-write-review-text">
              {Drupal.t('Write a review')}
            </div>
            <Popup
              open={isModalOpen}
              className="write_review"
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <React.Suspense fallback={<Loading />}>
                <WriteReviewForm
                  closeModal={() => this.closeModal(triggerCloseModal)}
                  formData={formFieldConfigs}
                />
              </React.Suspense>
            </Popup>
          </div>
        )}
      </WithModal>
    );
  }
}
