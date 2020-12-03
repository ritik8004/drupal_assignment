import React from 'react';
import StyleFinderTitle from '../../../utilities/style-finder-title';
import StyleFinderDesc from '../../../utilities/style-finder-description';
import StyleFinderSteps from '../../../utilities/style-finder-steps';
import StyleFinderSubTitle from '../../../utilities/style-finder-subtitle';
import StyleFinderList from '../../../utilities/style-finder-list';
import StyleFinderPara from '../../../utilities/style-finder-paragraph';

export default class StyleFinder extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  render() {
    return (
      <section className="style-finder-wrapper">
        <StyleFinderTitle>
          {Drupal.t('Style Finder')}
        </StyleFinderTitle>

        <StyleFinderDesc>
          {Drupal.t('Complete the steps below to find your best-fitting bra.')}
        </StyleFinderDesc>

        <StyleFinderSteps>
          {Drupal.t('Steps 1')}
        </StyleFinderSteps>

        <StyleFinderSubTitle>
          <div className="style-finder-choose-type">
            {Drupal.t('Choose your type placeholder')}
          </div>
        </StyleFinderSubTitle>

        <StyleFinderList>
          <li className="style-type">{Drupal.t('Bras')}</li>
          <li className="style-type">{Drupal.t('Sports Bras')}</li>
        </StyleFinderList>

        <StyleFinderSteps>
          {Drupal.t('Steps 2')}
        </StyleFinderSteps>

        <StyleFinderSubTitle>
          <div className="style-finder-choose-lining-level">
            {Drupal.t('Choose your lining level placeholder')}
          </div>
        </StyleFinderSubTitle>

        <StyleFinderList>
          <li className="style-lining-level">
            <div className="unlined">{Drupal.t('Unlined image placeholder')}</div>
            <StyleFinderPara>
              {Drupal.t('Nothing comes between you and your bra, but you\'ll still feel supported.')}
            </StyleFinderPara>
          </li>
          <li className="style-lining-level">
            <div className="lightly-lined">{Drupal.t('Lightly lined image placeholder')}</div>
            <StyleFinderPara>
              {Drupal.t('Just a little something for smooth shape, no show-through and plenty of support.')}
            </StyleFinderPara>
          </li>
          <li className="style-lining-level">
            <div className="push-up">{Drupal.t('Push up image placeholder')}</div>
            <StyleFinderPara>
              {Drupal.t('Comfortable, state-of-the-art padding for extra support and a subtle to sultry lift.')}
            </StyleFinderPara>
          </li>
        </StyleFinderList>

        <StyleFinderSteps>
          {Drupal.t('Steps 3')}
        </StyleFinderSteps>

        <StyleFinderSubTitle>
          <div className="style-finder-choose-coverage">
            {Drupal.t('Choose your coverage')}
          </div>
        </StyleFinderSubTitle>

        <StyleFinderList>
          <li className="style-coverage">
            <div className="full"><span>{Drupal.t('Full Coverage')}</span></div>
            <StyleFinderPara>
              {Drupal.t('Complete coverage and comfort, maximum support.')}
            </StyleFinderPara>
          </li>
          <li className="style-coverage">
            <div className="demi"><span>{Drupal.t('Demi')}</span></div>
            <StyleFinderPara>
              {Drupal.t('A little less coverage in a subtle shape.')}
            </StyleFinderPara>
          </li>
          <li className="style-coverage">
            <div className="plunge"><span>{Drupal.t('Plunge')}</span></div>
            <StyleFinderPara>
              {Drupal.t('A deep neckline that\'s the most revealing')}
            </StyleFinderPara>
          </li>
        </StyleFinderList>

      </section>
    );
  }
}
