import React from 'react';
import Popup from 'reactjs-popup';
import QRCode from 'react-qr-code';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';

class QrCodeDisplay extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  openModal = (e) => {
    e.preventDefault();
    const { benefitName, benefitType } = this.props;
    // Push qr code click to gtm when user clicks on view qr link.
    if (hasValue(benefitName) && hasValue(benefitType)) {
      Drupal.alshayaSeoGtmPushBenefitQrData(benefitName, benefitType);
    }
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
      memberId, qrCodeTitle, codeId, width, memberTitle,
    } = this.props;

    return (
      <>
        <div onClick={(e) => this.openModal(e)} className="qr-code-button">
          {memberTitle}
        </div>
        <Popup
          open={isModelOpen}
          className="qr-code-modal"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="qr-code-block">
            <div className="qr-code-title">
              <span>{getStringMessage('qr_code_title')}</span>
              <a className="close-modal" onClick={(e) => this.closeModal(e)} />
            </div>
            <div className="qr-img-block">
              <div className="qr-redeem">{(qrCodeTitle) ? getStringMessage('member_id_title') : getStringMessage('qr_code_redeem')}</div>
              <div className="img-container">
                <QRCode
                  size={width}
                  viewBox={`0 0 ${width} ${width}`}
                  value={memberId}
                />
              </div>
              <div className="my-membership-id">
                {memberId}
              </div>
            </div>
            {(qrCodeTitle && codeId) && (
              <div className="qr-img-block">
                <div className="qr-redeem">{qrCodeTitle}</div>
                <div className="img-container">
                  <QRCode
                    size={79}
                    viewBox="0 0 79 79"
                    value={codeId}
                  />
                </div>
                <div className="my-membership-id">
                  {codeId}
                </div>
              </div>
            )}
          </div>
        </Popup>
      </>
    );
  }
}

export default QrCodeDisplay;
