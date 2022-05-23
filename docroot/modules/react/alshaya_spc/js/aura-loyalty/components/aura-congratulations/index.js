import React from 'react';
import Popup from 'reactjs-popup';
import SectionTitle from '../../../utilities/section-title';
import AuraAppLinks from '../utilities/aura-app-links';
import getStringMessage from '../../../../../js/utilities/strings';

class AuraCongratulationsModal extends React.Component {
  constructor(props) {
    super(props);

    // By default congratulation popup will remain close.
    this.state = {
      showCongratulations: false,
    };
  }

  componentDidMount() {
    // Event listener to listen to actions on loyalty blocks.
    document.addEventListener('loyaltyStatusUpdated', this.toggleCongPopup, false);
  }

  // Event listener callback to update header states.
  toggleCongPopup = (data) => {
    const { showCongratulationsPopup } = data.detail;
    // Show congratulations popup only if showCongratulationsPopup is defined and true.
    if ((typeof showCongratulationsPopup !== 'undefined')
      && showCongratulationsPopup
    ) {
      this.setState({
        showCongratulations: true,
      });
    }
  }

  // Close the congratulation modal popup.
  closeCongratulationsModal = () => {
    this.setState({
      showCongratulations: false,
    });
  };

  render() {
    const { showCongratulations } = this.state;

    // Return null if congratulation popup state is closed.
    if (!showCongratulations) {
      return null;
    }

    return (
      <Popup
        className="aura-modal-congratulations"
        open={showCongratulations}
        closeOnEscape={false}
        closeOnDocumentClick={false}
      >
        <div className="aura-congratulations-modal">
          <div className="aura-modal-header">
            <SectionTitle>{getStringMessage('join_aura_congratulations_header')}</SectionTitle>
            <button type="button" className="close" onClick={() => this.closeCongratulationsModal()} />
          </div>
          <div className="aura-modal-body">
            <div className="congratulations-text">{getStringMessage('join_aura_congratulations_text')}</div>
            <div className="download-text">{getStringMessage('join_aura_congratulations_download_text')}</div>
            <div className="mobile-only"><AuraAppLinks /></div>
          </div>
        </div>
      </Popup>
    );
  }
}

export default AuraCongratulationsModal;
