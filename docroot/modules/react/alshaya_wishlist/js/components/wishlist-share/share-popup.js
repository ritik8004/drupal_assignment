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
    const { closeWishlistModal } = this.props;
    // If user simply clicks on close, we pass false as response.
    closeWishlistModal(false);
  }

  /**
   * Handler to copy the share link on clipboard.
   */
  copyShareLink = () => {
    // Set the copyLinkStatus to true when link is copied.
    // @todo: change the url with the actual share link.
    navigator.clipboard.writeText('https://www.google.com/');
    this.setState({ copyLinkStatus: true });
  }

  /**
   * Handler to open the email client with share link.
   */
  emailClickHandler = (e) => {
    // Open mailto with window location helper.
    // @todo: need to replace the share link in the email body text.
    window.location = `mailto:?subject=${encodeURIComponent(drupalSettings.wishlist.config.shareEmailSubject) || ''}&body=${encodeURIComponent(drupalSettings.wishlist.config.shareEmailMessage) || ''}`;
    e.preventDefault();
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
              {Drupal.t('Share all your faviourites with friends and family.')}
            </div>
            <div className="actions">
              <button
                type="button"
                className="email-share-link"
                onClick={(e) => this.emailClickHandler(e)}
              >
                {Drupal.t('Email')}
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
