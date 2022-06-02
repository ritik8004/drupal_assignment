import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getReturnConfirmationStrings } from '../../../utilities/return_confirmation_util';

const WhatsNextSection = () => {
  const confirmationStrings = getReturnConfirmationStrings();
  if (!hasValue(confirmationStrings)) {
    return null;
  }
  return (
    <div className="whats-next-section">
      <div className="whats-next-label">
        <div className="whats-next-title">{ Drupal.t("What's next?", {}, { context: 'online_returns' }) }</div>
      </div>
      <div className="whats-next-wrapper">
        {confirmationStrings.map((str) => (
          <div key={str.icon} className="item-list-wrapper">
            <ConditionalView condition={!str.hide_this_row}>
              <div className={`${str.icon}`} />
              <div className="whats-next-content">
                <div className="title">{str.title}</div>
                <div className="description">{parse(str.description.value)}</div>
              </div>
            </ConditionalView>
          </div>
        ))}
      </div>
    </div>
  );
};

export default WhatsNextSection;
