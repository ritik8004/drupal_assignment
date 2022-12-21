import React from 'react';
import parse from 'html-react-parser';
import DeliveryPropositionIcon from './DeliveryPropositionIcon';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const DeliveryPropositionItem = (props) => {
  const {
    icon_path: iconPath,
    main_text: mainText,
    sub_text: subText,
  } = props.data;

  let formattedMarkup = hasValue(mainText) ? `<b>${mainText}</b>` : '';
  // Append sub text separated by '-' if main text is not empty
  // and if main text is empty assign sub text only.
  if (hasValue(subText)) {
    formattedMarkup = hasValue(formattedMarkup) ? `${formattedMarkup} - ${subText}` : subText;
  }

  // Render the delivery proposition item if either mainText
  // or subText is not empty.
  if (hasValue(formattedMarkup)) {
    return (
      <div className="delivery-proposition-item">
        {hasValue(iconPath) && (
          <div className="delivery-proposition-icon">
            <DeliveryPropositionIcon iconPath={iconPath} />
          </div>
        )}
        <div className="delivery-proposition-text">
          {parse(formattedMarkup)}
        </div>
      </div>
    );
  }

  return '';
};

export default DeliveryPropositionItem;
