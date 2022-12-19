import React from 'react';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import {
  getWishlistLabel,
  getWishlistNotificationTime,
} from '../../../../js/utilities/wishlistHelper';
import getStringMessage from '../../../../js/utilities/strings';

export default class SharePopup extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // To identify if share link is copied or not.
      copyLinkStatus: false,
      hideCopyLinkText: false,
    };
  }

  /**
   * Close the modal with the parent state change.
   */
  closeModal = () => {
    const { closeWishlistShareModal } = this.props;
    // If user simply clicks on close, we pass false as response.
    closeWishlistShareModal(false);
  }

  /**
   * Handler to copy the share link on clipboard.
   */
  copyShareLink = () => {
    // Push copy link button click to gtm.
    Drupal.alshayaSeoGtmPushShareWishlist('copy link');

    const { wishlistShareLink } = this.props;
    // Set the copyLinkStatus to true when link is copied.
    navigator.clipboard.writeText(wishlistShareLink);
    this.setState({ copyLinkStatus: true });
    // Set timer for the link copy text.
    this.setTimer();
  }

  /**
   * Set timer for the link copy text.
   */
  setTimer() {
    if (this.timer != null) {
      clearTimeout(this.timer);
    }

    // Hide copy text after certain milliseconds.
    this.timer = setTimeout(() => {
      this.setState({
        hideCopyLinkText: true,
      });
      this.timer = null;
    }, getWishlistNotificationTime());
  }

  /**
   * Handler to open the email client with share link.
   */
  emailClickHandler = (e) => {
    // Push email button click to gtm.
    Drupal.alshayaSeoGtmPushShareWishlist('email');

    e.preventDefault();
    const { wishlistShareLink } = this.props;
    const shareEmailSubject = drupalSettings.wishlist.config.shareEmailSubject || '';
    let shareEmailMessage = drupalSettings.wishlist.config.shareEmailMessage || '';

    // Replace the wishlist share link with placeholder.
    if (shareEmailMessage) {
      shareEmailMessage = shareEmailMessage.replace('[alshaya_wishlist:wishlist_share_url]', wishlistShareLink);
    }

    // Open mailto with window location helper.
    window.location = `mailto:?subject=${encodeURIComponent(shareEmailSubject)}&body=${encodeURIComponent(shareEmailMessage)}`;
  }

  render() {
    const { copyLinkStatus, hideCopyLinkText } = this.state;

    return (
      <div className="wishlist-share-popup-container">
        <Popup
          open
          className="wishlist-share"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="wishlist-share-popup-block">
            <a
              className="close-modal"
              onClick={() => this.closeModal()}
            >
              {Drupal.t('Close')}
            </a>
            <div className="header">{getStringMessage('share_your_list', { '@wishlist_label': getWishlistLabel() })}</div>
            <div className="content">
              {getStringMessage('share_your_list_text', { '@wishlist_label': getWishlistLabel() })}
            </div>
            <div className="actions">
              <button
                type="button"
                className="email-share-link"
                onClick={(e) => this.emailClickHandler(e)}
              >
                {getStringMessage('wishlist_email_button')}
              </button>
              <ConditionalView condition={!hideCopyLinkText}>
                {copyLinkStatus
                  ? (
                    <span
                      className="copy-share-link link-copied"
                    >
                      {getStringMessage('wishlist_link_copied')}
                    </span>
                  )
                  : (
                    <button
                      type="button"
                      className="copy-share-link"
                      onClick={this.copyShareLink}
                    >
                      {getStringMessage('wishlist_link_copy')}
                    </button>
                  )}
              </ConditionalView>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
