import React from 'react';
import { getLanguageCode, getbazaarVoiceSettings } from '../../../../../../utilities/api/request';

const bazaarVoiceSettings = getbazaarVoiceSettings();
const tncUri = `/${getLanguageCode()}${bazaarVoiceSettings.reviews.bazaar_voice.write_review_tnc}`;
const guidlinesUri = `/${getLanguageCode()}${bazaarVoiceSettings.reviews.bazaar_voice.write_review_guidlines}`;
const FormLinks = ({
  tnc, reviewGuide,
}) => (
  <div className="link-block">
    <div className="static-link"><a href={tncUri} target="_blank" rel="noopener noreferrer">{tnc}</a></div>
    <div className="static-link"><a href={guidlinesUri} target="_blank" rel="noopener noreferrer">{reviewGuide}</a></div>
  </div>
);

export default FormLinks;
