import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import GridButtons from '../grid-buttons';

export default function GridAndCount(props) {
  const { children } = props;
  const { hideGridSwitch } = drupalSettings.algoliaSearch;
  return (
    <div className="block block-alshaya-grid-count-block">
      <div className="total-result-count">
        <div className="view-header search-count tablet">
          {children}
        </div>
      </div>
      <ConditionalView condition={!hideGridSwitch}>
        <GridButtons />
      </ConditionalView>
    </div>
  );
}
