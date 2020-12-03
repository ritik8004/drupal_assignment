import React from 'react';
import StyleFinderTitle from '../../../utilities/style-finder-title';
import StyleFinderDesc from '../../../utilities/style-finder-description';
import StyleFinderSteps from '../../../utilities/style-finder-steps';
import StyleFinderSubTitle from '../../../utilities/style-finder-subtitle';

export default class StyleFinder extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  render() {
    return (
      <section className="style-finder-wrapper">
        <div className="style-finder-heading-wrapper">
          <StyleFinderTitle>
            {Drupal.t('Style Finder')}
          </StyleFinderTitle>

          <StyleFinderDesc>
            {Drupal.t('Complete the steps below to find your best-fitting bra.')}
          </StyleFinderDesc>
        </div>
        <div className="style-finder-step-wrapper">
          <StyleFinderSteps>
            {Drupal.t('Steps 1')}
          </StyleFinderSteps>

          <StyleFinderSubTitle className="style-finder-choose-type">
            {Drupal.t('Choose your type')}
          </StyleFinderSubTitle>

          <ul className="style-finder-list style-finder-type-list">
            <li className="style-type">{Drupal.t('Bras')}</li>
            <li className="style-type">{Drupal.t('Sports Bras')}</li>
          </ul>
        </div>
        <div className="style-finder-step-wrapper">
          <StyleFinderSteps>
            {Drupal.t('Steps 2')}
          </StyleFinderSteps>

          <StyleFinderSubTitle className="style-finder-choose-lining-level">
            {Drupal.t('Choose your lining level')}
          </StyleFinderSubTitle>

          <ul className="style-finder-list style-finder-lining-list">
            <li className="style-finder-list-item">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/p/280x373/tif/59/19/59196aba1c544b40aa9444744794727c/111820074Z6W_OF_F.jpg" />
              </div>
              <div className="style-finder-list-title">
                {Drupal.t('Unlined')}
              </div>
              <div className="style-finder-list-text">
                {Drupal.t('Nothing comes between you and your bra, but you\'ll still feel supported.')}
              </div>
            </li>
            <li className="style-finder-list-item">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/p/280x373/tif/f4/67/f4679a9b946e452e9a244c78e9741f15/111782234X4O_OF_F.jpg" />
              </div>
              <div className="style-finder-list-title">
                {Drupal.t('Lightly Lined')}
              </div>
              <div className="style-finder-list-text">
                {Drupal.t('Just a little something for smooth shape, no show-through and plenty of support.')}
              </div>
            </li>
            <li className="style-finder-list-item">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/p/280x373/tif/7b/2d/7b2d2355995241b3b3219ba161c6becd/1118200251MM_OF_F.jpg" />
              </div>
              <div className="style-finder-list-title">
                {Drupal.t('Push-Up')}
              </div>
              <div className="style-finder-list-text">
                {Drupal.t('Comfortable, state-of-the-art padding for extra support and a subtle to sultry lift.')}
              </div>
            </li>
          </ul>
        </div>
        <div className="style-finder-step-wrapper style-finder-step-coverage-wrapper">
          <StyleFinderSteps>
            {Drupal.t('Steps 3')}
          </StyleFinderSteps>

          <StyleFinderSubTitle className="style-finder-choose-coverage">
            {Drupal.t('Choose your coverage')}
          </StyleFinderSubTitle>

          <ul className="style-finder-list style-finder-step-coverage-wrapper">
            <li className="style-coverage">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/assets/cms-components/vs-bra-finder/assets/0818-bra-guide-coverage-perfect-coverage.aa99069e.png" />
              </div>
              <div className="style-finder-list-title">{Drupal.t('Full Coverage')}</div>
              <div className="style-finder-list-text">
                {Drupal.t('Complete coverage and comfort, maximum support.')}
              </div>
            </li>
            <li className="style-coverage">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/assets/cms-components/vs-bra-finder/assets/0818-bra-guide-coverage-demi.17572faa.png" />
              </div>
              <div className="style-finder-list-title">{Drupal.t('Demi')}</div>
              <div className="style-finder-list-text">
                {Drupal.t('A little less coverage in a subtle shape.')}
              </div>
            </li>
            <li className="style-coverage">
              <div className="style-finder-list-image">
                <img src="https://www.victoriassecret.com/assets/cms-components/vs-bra-finder/assets/0818-bra-guide-coverage-plunge.fe3c2cc4.png" />
              </div>
              <div className="style-finder-list-title">{Drupal.t('Plunge')}</div>
              <div className="style-finder-list-text">
                {Drupal.t('A deep neckline that\'s the most revealing')}
              </div>
            </li>
          </ul>
        </div>
      </section>
    );
  }
}
