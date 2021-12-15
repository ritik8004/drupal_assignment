import React from 'react';
import Popup from 'reactjs-popup';

export default class SharePopup extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // To identify if share link is copied or not.
      copyLinkStatus: false,
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
    const { wishlistShareLink } = this.props;
    // Set the copyLinkStatus to true when link is copied.
    navigator.clipboard.writeText(wishlistShareLink);
    this.setState({ copyLinkStatus: true });
  }

  /**
   * Handler to open the email client with share link.
   */
  emailClickHandler = (e) => {
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
    const { copyLinkStatus } = this.state;

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
            <div className="header">{Drupal.t('Share Your List', {}, { context: 'wishlist' })}</div>
            <div className="content">
              {Drupal.t('Share all your favourites with friends and family.')}
            </div>
            <div className="actions">
              <button
                type="button"
                className="email-share-link"
                onClick={(e) => this.emailClickHandler(e)}
              >
                {Drupal.t('Email', {}, { context: 'wishlist' })}
              </button>
              {copyLinkStatus
                ? (
                  <span
                    className="copy-share-link link-copied"
                  >
                    {Drupal.t('Link Copied', {}, { context: 'wishlist' })}
                  </span>
                )
                : (
                  <button
                    type="button"
                    className="copy-share-link"
                    onClick={this.copyShareLink}
                  >
                    {Drupal.t('Copy Link', {}, { context: 'wishlist' })}
                  </button>
                )}
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
