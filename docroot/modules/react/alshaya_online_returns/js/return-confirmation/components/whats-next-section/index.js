import React from 'react';
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
        {confirmationStrings.map((section) => (
          <div key={section.title} className="item-list-wrapper">
            <ConditionalView condition={section.hide_row}>
              <div className={`${section.icon_class}}`} />
              <div className="title">{section.title}</div>
              <div className="description">{section.description}</div>
            </ConditionalView>
          </div>
        ))}
      </div>
    </div>
  );
};

export default WhatsNextSection;
