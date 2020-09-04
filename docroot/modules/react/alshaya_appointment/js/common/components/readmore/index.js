import React from 'react';
import getStringMessage from '../../../../../js/utilities/strings';
import ConditionalView from '../conditional-view';

const descriptionThreshold = 150;

export default class ReadMore extends React.Component {
  constructor(props) {
    super(props);
    this.readMoreRef = React.createRef();
    this.state = {
      open: false,
      desc: '',
    };
  }

  componentDidMount() {
    const { description } = this.props;
    const stateDesc = this.getTrimmedDescription(description);
    // Assign description in state.
    this.setState({
      desc: stateDesc,
    });
  }

  componentDidUpdate(prevProps) {
    const { description } = this.props;
    if (description !== prevProps.description) {
      const stateDesc = this.getTrimmedDescription(description);
      // Assign description in state and closing the read more.
      this.closeReadMore(stateDesc);
    }
  }

  closeReadMore = (stateDesc) => {
    this.setState({
      desc: stateDesc,
      open: false,
    });
  };

  /**
   * Trim description if it is beyond our threshold.
   *
   * @param description
   * @returns {string|*}
   */
  getTrimmedDescription = (description) => {
    if (description.length > descriptionThreshold && window.innerWidth < 768) {
      return `${description.substring(0, descriptionThreshold)} ...`;
    }
    return description;
  }

  /**
   * Click handler for read more link.
   */
  expandContent = () => {
    const { open } = this.state;
    const { description } = this.props;

    if (open) {
      const stateDesc = this.getTrimmedDescription(description);
      this.setState({
        open: false,
        desc: stateDesc,
      });
    } else {
      this.setState({
        open: true,
        desc: description,
      });
    }
  };

  render() {
    const { open, desc } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'expanded' : '';
    // Add link text.
    const linkText = open === true ? getStringMessage('show_less') : getStringMessage('read_more');
    // Hide/Show Read more link.
    let showReadMoreClass = '';
    if (desc.length < descriptionThreshold) {
      showReadMoreClass = 'hide-link';
    }

    // If description is empty.
    if (desc.length < 1) {
      return (
        <div className="read-more-less empty" />
      );
    }

    return (
      <div className={`read-more-less ${expandedState}`}>
        <span ref={this.readMoreRef} className="read-more-content short-text">
          { desc }
        </span>
        <ConditionalView condition={window.innerWidth < 768}>
          <a className={`readmore-link readMoreText ${showReadMoreClass}`} onClick={() => this.expandContent()}>
            { linkText }
          </a>
        </ConditionalView>
      </div>
    );
  }
}
