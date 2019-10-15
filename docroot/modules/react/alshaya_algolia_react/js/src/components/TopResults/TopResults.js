import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const TopResults = ({query, children}) => {
  const el = useRef(document.createElement('div'));

  const backIcon = useRef(document.createElement('span'));
  backIcon.current.classList.add('algolia-search-back-icon');

  const clearIcon = useRef(document.createElement('span'));
  clearIcon.current.classList.add('algolia-search-cleartext-icon');

  const [dynamic] = useState(!el.current.parentElement);
  useEffect(() => {
    if (dynamic) {
      el.current.id = 'top-results';
      const autosuggestContainer = document.querySelector('.react-autosuggest__container');
      autosuggestContainer.appendChild(el.current);
      autosuggestContainer.appendChild(backIcon.current);
      autosuggestContainer.appendChild(clearIcon.current);
    }
    return () => {
      if (dynamic && el.current.parentElement) {
        console.log(el.current);
        el.current.parentElement.removeChild(el.current);
      }
    };
  }, [query]);

  return createPortal(children, el.current);
};

export default TopResults;
