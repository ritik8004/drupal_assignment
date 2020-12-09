import React from 'react';
import Slider from 'react-slick';
import StyleFinderTitle from '../../../utilities/style-finder-title';
import StyleFinderDesc from '../../../utilities/style-finder-description';
import StyleFinderSteps from '../../../utilities/style-finder-steps';
import StyleFinderSubTitle from '../../../utilities/style-finder-subtitle';
import StyleFinderListItem from '../../../utilities/style-finder-list-item';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import ConditionalView from '../../../common/components/conditional-view';
import StyleFinderProduct from '../stylefinder-product';

export default class StyleFinder extends React.Component {
  constructor() {
    super();
    this.state = {
      step: [],
      answerSelected: [],
      productRecommendation: {},
      seeMoreUrl: '',
    };
    this.next = this.next.bind(this);
    this.previous = this.previous.bind(this);
  }

  /**
   * Add Step 1 details from drupalSettings to state.
   */
  componentDidMount() {
    const { quizDetails } = drupalSettings.styleFinder;
    const step = [quizDetails.question[0]];
    this.setState({
      step,
    });
    document.addEventListener('dyGetProductRecommendation', this.getProducts, false);
  }

  getProducts = (event) => {
    if (event !== undefined) {
      const { productData, error } = event.detail;
      if (error) {
        this.setState({
          productRecommendation: false,
        });
      }
      if (Object.keys(productData.strategies[0].items).length > 0) {
        this.setState({
          productRecommendation: productData,
        });
      } else {
        this.setState({
          productRecommendation: false,
        });
      }
    }
  }

  /**
   * Gets parent li tag to set active.
   */
  findAncestor = (el, cls) => {
    const element = el;
    let e = el;
    while (!e.classList.contains(cls)) {
      e = e.parentElement;
    }
    return (el === null) ? element : e;
  }

  /**
   * Handle user Choice on every step.
   */
  handleStepSubmit = (e, answer, attrCode, choice, counter) => {
    // Set choice as active.
    e.preventDefault();
    const element = this.findAncestor(e.target, 'list-item');
    if (element !== undefined) {
      element.parentElement.childNodes.forEach((child) => {
        child.classList.remove('active');
      });
      element.classList.add('active');
    }

    // Get step and selected answer from state.
    const { step, answerSelected } = this.state;
    step.length = counter;
    answerSelected.length = counter - 1;
    const selected = {
      attrCode,
      choice,
    };
    answerSelected.push(selected);
    // Push answer choice to the state.
    this.setState({
      answerSelected,
    });

    // Get see more style url.
    const seeMore = step[counter - 1].answer[answer].see_more_reference;
    if (seeMore) {
      this.setState({
        seeMoreUrl: seeMore,
      });
    }
    // Get next question step choice.
    const stepDetails = step[step.length - 1];
    if (stepDetails.answer[answer].question !== undefined
      && stepDetails.answer[answer].question.length > 0) {
      // Push the next question to the state for render.
      step.push(stepDetails.answer[answer].question[0]);
      this.setState({
        step,
      });
      this.setState({
        productRecommendation: {},
      });
    } else {
      // Update state if questions end.
      this.setState({
        step,
      });

      // Filter Rule.
      let realtimeRules = [];

      // Filter rule conditions based on selections.
      const conditions = [];
      answerSelected.forEach((item) => {
        const condition = {
          field: item.attrCode, // Condition
          arguments: [{
            action: 'IS', // Action type IS / IS_NOT / CONTAINS / EQ / GT / GTE / LT / LTE
            value: item.choice, // Value of condition
          }],
        };
        conditions.push(condition);
      });

      realtimeRules = [{
        id: -1,
        query: {
          conditions,
        },
        type: 'include', // Include or exclude
        slots: [], // Position in widget
      }];

      // DY API for Product recommendation with real time rules.
      const { dyStrategyId } = drupalSettings.styleFinder;
      window.DYO.recommendations.getRcomData(dyStrategyId,
        { maxProducts: 50, realtimeRules }, (err, data) => {
          let error = false;
          if (err) {
            error = true;
          }
          dispatchCustomEvent('dyGetProductRecommendation', {
            productData: data,
            error,
          });
        });
    }
  };

  /**
   * Renders Option list for every step.
   */
  renderStep = (stepDetails, counter) => {
    const { answer } = stepDetails;
    let optionListClass = 'style-finder-lining-list';
    let optionList = '';
    optionList = Object.keys(answer).map(function listOptionsStep1(item) {
      return (
        <StyleFinderListItem
          key={item.nid}
          answer={answer[item]}
          handleStepSubmit={this.handleStepSubmit}
          counter={counter}
        />
      );
    }, this);
    if (counter === 1) {
      optionListClass = 'style-finder-type-list';
      optionList = Object.keys(answer).map(function listOptionsStep(index) {
        return (
          <li
            className="list-item"
            key={index.nid}
            onClick={(e) => this.handleStepSubmit(
              e,
              index,
              answer[index].attrCode,
              answer[index].choice, 1,
            )}
          >
            {answer[index].title}
          </li>
        );
      }, this);
    } else if (counter === 2) {
      optionListClass = 'style-finder-lining-list';
    } else if (counter === 3) {
      optionListClass = 'style-finder-step-coverage-wrapper';
    }

    return (
      <div className="style-finder-step-wrapper" key={stepDetails.ques_instruction}>
        <StyleFinderSteps>
          {stepDetails.ques_instruction}
        </StyleFinderSteps>

        <StyleFinderSubTitle className="style-finder-choose-lining-level">
          {stepDetails.title}
        </StyleFinderSubTitle>

        <ul className={`style-finder-list ${optionListClass}`}>
          {optionList}
        </ul>
      </div>
    );
  }

  next = () => {
    this.slider.slickNext();
  }

  previous = () => {
    this.slider.slickPrev();
  }

  render() {
    const { quizDetails } = drupalSettings.styleFinder;
    const { step, productRecommendation, seeMoreUrl } = this.state;

    // Prepare steps for render.
    const otherSteps = [];
    let j = 1;
    if (step.length > 0) {
      for (let i = 0; i < step.length; i++) {
        otherSteps.push(this.renderStep(step[i], j));
        j += 1;
      }
    }

    const settings = {
      className: 'center',
      centerMode: true,
      infinite: true,
      centerPadding: '60px',
      slidesToShow: 3,
      speed: 500,
    };
    let items = [];
    let startegyId = '';
    if (Object.keys(productRecommendation).length > 0) {
      items = productRecommendation.strategies[0].items;
      startegyId = productRecommendation.wId;
    }

    return (
      <section className="style-finder-wrapper">
        <div className="style-finder-heading-wrapper">
          <StyleFinderTitle>
            {quizDetails.quiz_title}
          </StyleFinderTitle>
          <StyleFinderDesc>
            {quizDetails.quiz_instruction}
          </StyleFinderDesc>
        </div>
        {otherSteps.map((item) => item)}
        <div className="style-finder-heading-wrapper">
          <StyleFinderSteps>
            {Drupal.t('suggestions for you')}
          </StyleFinderSteps>
          <ConditionalView condition={(Object.keys(productRecommendation).length === 0)
          && (productRecommendation !== false)}
          >
            <div id="bf-results-placeholder">
              <ul>
                <li className="bf-msg">
                  <div className="bf-msg-copy">
                    <p>{Drupal.t('You’re so close!')}</p>
                    <p>{Drupal.t('Complete the steps above to find your perfect bra.')}</p>
                  </div>
                </li>
              </ul>
            </div>
          </ConditionalView>
          <ConditionalView condition={(productRecommendation === false)}>
            <div id="bf-results-placeholder">
              <ul>
                <li className="bf-msg">
                  <div className="bf-msg-copy">
                    <p>{Drupal.t('No recommendations found.')}</p>
                  </div>
                </li>
              </ul>
            </div>
          </ConditionalView>
          <ConditionalView condition={(Object.keys(productRecommendation).length > 0)}>
            <span>{Drupal.t('Here are the personalized bra styles we think you’ll love!')}</span>
            <div
              className="dy-products"
              data-dy-widget-id={productRecommendation.wId}
              data-dy-feed-id={productRecommendation.fId}
            >
              <Slider ref={(c) => { this.slider = c; }} {...settings} className="products-slider">
                {
                  items.map((item) => (
                    <StyleFinderProduct
                      key={item.sku}
                      item={item}
                      strategyId={startegyId}
                    />
                  ))
                }
              </Slider>
              <div style={{ textAlign: 'center' }}>
                <button type="button" className="button" onClick={this.previous}>
                  Previous
                </button>
                <button type="button" className="button" onClick={this.next}>
                  Next
                </button>
              </div>
            </div>
          </ConditionalView>
        </div>
        <div className="see-more">
          {(seeMoreUrl)
            ? (<a href={seeMoreUrl} className="see-more-bra">{Drupal.t('See More Styles')}</a>)
            : (<a href="#" className="see-more-bra">{Drupal.t('See More Styles')}</a>)}
        </div>
      </section>
    );
  }
}
