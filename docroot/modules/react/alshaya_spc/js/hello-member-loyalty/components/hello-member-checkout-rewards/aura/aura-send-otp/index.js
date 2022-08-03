import React from 'react';
import { showError } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';
import { sendOtp } from '../../../../../../../js/utilities/otp_helper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraFormModalMessage from '../../../../../aura-loyalty/components/aura-forms/aura-form-modal-message';
import { getInlineErrorSelector } from '../../../../../aura-loyalty/components/utilities/link_card_sign_up_modal_helper';
import TextField from '../../../../../utilities/textfield';
import ToolTip from '../../../../../utilities/tooltip';
import AuraVerifyOTP from '../aura-verify-otp';

class AuraSendOTP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      otpRequested: false,
      messageType: null,
      messageContent: null,
    };
  }

  componentDidMount() {
    this.sendOtp();
  }

  resetModalMessages = (messageType, messageContent) => {
    this.setState({
      messageType,
      messageContent,
    });
  }

  sendOtp = () => {
    const { mobile } = this.props;
    showFullScreenLoader();
    const apiData = sendOtp(mobile, 'link');
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.status) {
          // Once we get a success response that OTP is sent, we update state,
          // to show the otp fields.
          this.setState({
            otpRequested: true,
          });
          removeFullScreenLoader();
          return;
        }

        if (result.data && result.data.error) {
          showError(
            getInlineErrorSelector('mobile').mobile,
            getStringMessage(result.data.error_message),
          );
          removeFullScreenLoader();
          return;
        }

        this.setState({
          messageType: 'error',
          messageContent: getStringMessage('form_error_send_otp_failed_message'),
        });
        removeFullScreenLoader();
      });
    }
  }

  render() {
    const {
      otpRequested,
      messageType,
      messageContent,
    } = this.state;

    const { mobile } = this.props;

    if (!otpRequested) {
      return null;
    }

    return (
      <div className="aura-send-otp">
        <div className="aura-modal-form">
          <div className="aura-enter-otp">
            {getStringMessage('enter_otp')}
            <ToolTip enable>{getStringMessage('otp_send_message')}</ToolTip>
          </div>
          <div className="aura-modal-form-items">
            <div className="otp-field-section">
              <TextField
                type="text"
                required={false}
                name="otp"
                label={getStringMessage('otp_label')}
              />
              <AuraVerifyOTP
                mobile={mobile}
                resetModalMessages={this.resetModalMessages}
              />
              <div className="aura-otp-submit-description">
                <span
                  className="resend-otp"
                  onClick={this.sendOtp}
                >
                  {getStringMessage('resend_code')}
                </span>
              </div>
              <div className="aura-form-messages-container">
                <AuraFormModalMessage
                  messageType={messageType}
                  messageContent={messageContent}
                />
              </div>
              <div className="otp-sent-to-mobile-label">
                <span>
                  {getStringMessage('aura_otp_sent_to_mobile', {
                    '@mobile': mobile,
                  })}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraSendOTP;
