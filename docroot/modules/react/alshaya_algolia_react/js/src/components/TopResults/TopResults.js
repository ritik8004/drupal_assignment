import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const TopResults = ({query, children}) => {
  const el = useRef(document.createElement('div'));

  const [dynamic] = useState(!el.current.parentElement);
  useEffect(() => {
    if (dynamic) {
      el.current.id = 'top-results';
      document.querySelector('.react-autosuggest__container').appendChild(el.current);
    }
    return () => {
      if (dynamic && el.current.parentElement) {
        el.current.parentElement.removeChild(el.current);
      }
    };
  }, [query]);

  return createPortal(children, el.current);
};

export default TopResults;
