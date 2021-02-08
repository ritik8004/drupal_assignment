import React from 'react';
import WriteReviewLink from './WriteReviewLink';
import Modal from './Model';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../alshaya_spc/js/utilities/checkout_util';
import formData from '../../../utilities/api/formData';

export default class WriteReview extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isShown: false,
      formFieldConfigs: '',
    };
  }

  /**
   * Get form fields from bazaarVoice.
   */
  componentDidMount() {
    showFullScreenLoader();
    const apiUri = '/bv-form-config';
    const apiData = formData(apiUri);
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

  showModal = () => {
    this.setState({ isShown: true }, () => {
      this.closeButton.focus();
    });
    this.toggleScrollLock();
  };

  closeModal = () => {
    this.setState({ isShown: false });
    this.WriteReviewLink.focus();
    this.toggleScrollLock();
  };

  onKeyDown = (event) => {
    if (event.keyCode === 27) {
      this.closeModal();
    }
  };

  onClickOutside = (event) => {
    if (this.modal && this.modal.contains(event.target)) return;
    this.closeModal();
  };

  toggleScrollLock = () => {
    document.querySelector('html').classList.toggle('scroll-lock');
  };

  render() {
    const {
      writeReivewText,
    } = this.props;
    const {
      isShown,
      formFieldConfigs,
    } = this.state;
    return (
      <>
        <WriteReviewLink
          showModal={this.showModal}
          buttonRef={(n) => { this.WriteReviewLink = n; }}
          writeReivewText={writeReivewText}
        />
        {isShown ? (
          <Modal
            modalRef={(n) => { this.modal = n; }}
            buttonRef={(n) => { this.closeButton = n; }}
            closeModal={this.closeModal}
            onKeyDown={this.onKeyDown}
            onClickOutside={this.onClickOutside}
            formData={formFieldConfigs}
          />
        ) : null}
      </>
    );
  }
}
