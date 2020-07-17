import React from 'react';

export default class ReadMore extends React.Component {
  constructor(props) {
    super(props);
    this.readMoreRef = React.createRef();
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    const maxHeight = `${this.readMoreRef.current.offsetHeight}px`;
    this.readMoreRef.current.setAttribute('data-max-height', maxHeight);
    this.readMoreRef.current.classList.add('max-height-processed');
  }

  expandContent = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.readMoreRef.current.style.removeProperty('max-height');
    } else {
      this.setState({
        open: true,
      });
      const maxHeight = this.readMoreRef.current.getAttribute('data-max-height');
      this.readMoreRef.current.style.maxHeight = maxHeight;
    }
  };

  render() {
    const { description } = this.props;
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'expanded' : '';
    // Add link text.
    const linkText = open === true ? Drupal.t('Show less') : Drupal.t('Read More');
    // If description is empty.
    if (description.length < 1) {
      return (
        <div className="read-more-less empty" />
      );
    }

    return (
      <div className={`read-more-less ${expandedState}`}>
        <span ref={this.readMoreRef} className="read-more-content short-text">
          { description }
        </span>
        <a className="readmore-link readMoreText" onClick={() => this.expandContent()}>
          { linkText }
        </a>
      </div>
    );
  }
}
