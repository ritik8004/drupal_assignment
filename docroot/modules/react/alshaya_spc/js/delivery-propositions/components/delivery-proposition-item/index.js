import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const DeliveryPropositionItem = (props) => {
  const {
    data: {
      icon_path: iconPath,
      main_text: mainText,
      sub_text: subText,
    },
  } = props;

  // Don't render the item if main text is not available.
  if (!hasValue(mainText)) {
    return null;
  }
  // Append sub text separated by '-' if sub text is not empty
  // and if main text is empty assign sub text only.
  let formattedMarkup = `<b>${mainText}</b>`;
  if (hasValue(subText)) {
    formattedMarkup = `${formattedMarkup} - ${subText}`;
  }

  return (
    <div className="delivery-proposition-item">
      {hasValue(iconPath) && (
        <div className="delivery-proposition-icon">
          <img src={iconPath} height="30px" width="50px" loading="lazy" />
        </div>
      )}
      <div className="delivery-proposition-text">
        {parse(formattedMarkup)}
      </div>
    </div>
  );
};

export default DeliveryPropositionItem;
