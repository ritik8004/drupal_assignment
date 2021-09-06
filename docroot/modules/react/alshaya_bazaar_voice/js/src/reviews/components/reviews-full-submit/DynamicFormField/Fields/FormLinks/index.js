import React from 'react';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../../../../utilities/api/request';

const FormLinks = ({
  tnc, reviewGuide, productId,
}) => {
  const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
  const tncUri = `/${getLanguageCode()}/${bazaarVoiceSettings.reviews.bazaar_voice.write_review_tnc}`;
  const guidlinesUri = `/${getLanguageCode()}/${bazaarVoiceSettings.reviews.bazaar_voice.write_review_guidlines}`;
  return (
    <div className="link-block">
      <div className="static-link"><a href={tncUri} target="_blank" rel="noopener noreferrer">{tnc}</a></div>
      <div className="static-link"><a href={guidlinesUri} target="_blank" rel="noopener noreferrer">{reviewGuide}</a></div>
    </div>
  );
};

export default FormLinks;
