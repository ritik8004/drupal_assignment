import React from 'react';
import { getLoyaltyPageContent, isMyAuraContext } from '../../../utilities/aura_utils';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import LoyaltyPageContent from '../loyalty-page-content';

class LoyaltyClubBenefits extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      htmlContent: null,
    };
  }

  componentDidMount() {
    // Get the static html content from the api.
    const auraInfo = getLoyaltyPageContent();
    if (auraInfo instanceof Promise) {
      auraInfo.then((response) => {
        // Update state only if html value is available.
        if (hasValue(response) && hasValue(response.html)) {
          this.setState({
            htmlContent: response.html,
          });
        }
      });
    }
  }

  render() {
    const {
      active,
    } = this.props;

    const {
      htmlContent,
    } = this.state;

    return (
      <div className={`loyalty-club-details-wrapper loyalty-tab-content fadeInUp${active}`} style={{ animationDelay: '0.4s' }}>
        {isMyAuraContext() && (
          <LoyaltyPageContent
            htmlContent={htmlContent}
          />
        )}
      </div>
    );
  }
}

export default LoyaltyClubBenefits;
