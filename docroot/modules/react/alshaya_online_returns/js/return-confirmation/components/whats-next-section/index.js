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
      <div className="section-title">
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
