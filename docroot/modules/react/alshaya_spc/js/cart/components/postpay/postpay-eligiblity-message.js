import React from 'react';
import parse from 'html-react-parser';

const PostpayEligiblityMessage = ({ text }) => (
  <div className={`spc-messages-container spc-postpay-banner ${drupalSettings.postpay_widget_info.postpay_mode_class}`}>
    <div id="postpay-eligibility-message" style={{ display: 'none' }}>
      { parse(text) }
    </div>
  </div>
);

export default PostpayEligiblityMessage;
