import React from 'react';
import StyleFinderTitle from '../../../utilities/style-finder-title';
import StyleFinderDesc from '../../../utilities/style-finder-description';
import StyleFinderSteps from '../../../utilities/style-finder-steps';
import StyleFinderSubTitle from '../../../utilities/style-finder-subtitle';
import StyleFinderListItem from '../../../utilities/style-finder-list-item';

export default class StyleFinder extends React.Component {
  constructor() {
    super();
    this.state = {
      step: [],
      answerSelected: [],
    };
  }

  componentDidMount() {
    const { quizDetails } = drupalSettings.styleFinder;
    const step = [quizDetails.question[0]];
    this.setState({
      step,
    });
  }

  findAncestor = (el, cls) => {
    const element = el;
    let e = el;
    while (!e.classList.contains(cls)) {
      e = e.parentElement;
    }
    return (el === null) ? element : e;
  }

  handleStepSubmit = (e, answer, choice, counter) => {
    e.preventDefault();
    const element = this.findAncestor(e.target, 'list-item');
    if (element !== undefined) {
      element.parentElement.childNodes.forEach((child) => {
        child.classList.remove('active');
      });
      element.classList.add('active');
    }
    let { step, answerSelected } = this.state;
    step.length = counter;
    answerSelected.length = counter - 1;
    this.setState((state) => {
      answerSelected = state.answerSelected.concat(choice);
      return {
        answerSelected,
      };
    });
    const stepDetails = step[step.length - 1];
    if (stepDetails.answer[answer].question !== undefined
      && stepDetails.answer[answer].question.length > 0) {
      this.setState((state) => {
        step = state.step.concat(stepDetails.answer[answer].question[0]);
        return {
          step,
        };
      });
    } else {
      this.setState({
        step,
      });
    }
  };

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
            onClick={(e) => this.handleStepSubmit(e, index, answer[index].choice, 1)}
          >
            {answer[index].title}
          </li>
        );
      }, this);
    } else if (counter === 2) {
      optionListClass = 'style-finder-lining-list';
    } else if (counter === 3) {
      optionListClass = 'style-finder-bra-coverage-list';
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

  render() {
    const { quizDetails } = drupalSettings.styleFinder;
    const { step } = this.state;
    const otherSteps = [];
    let j = 1;
    if (step.length > 0) {
      for (let i = 0; i < step.length; i++) {
        otherSteps.push(this.renderStep(step[i], j));
        j += 1;
      }
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
      </section>
    );
  }
}
