(function (settings, Popper) {

  function getFootNote(tuneName, id) {
    let footNotes = settings.editorjs.tunes[tuneName] || [];

    for (const tune of footNotes) {
      if (tune.hasOwnProperty('id') && tune.id === id) {
        return tune.content || false;
      }
    }

    return false;
  }

  document.addEventListener('click', e => {
    if (!e.target.matches('sup[data-tune]')) {
      let popup = document.querySelector('span.footnote_popup:not(.visually-hidden)');

      if (popup) {
        popup.classList.toggle('visually-hidden', true);
        return;
      }

      return;
    }

    let tune = e.target.getAttribute('data-tune'),
      id = e.target.getAttribute('data-id');
    let popup = document.querySelector(`span.footnote_popup[id=${id}]`);

    if (popup) {
      popup.classList.toggle('visually-hidden', false);
      return;
    }

    let footNote = getFootNote(tune, id);
    popup = document.createElement('span');
    popup.classList.add('footnote_popup');
    popup.setAttribute('id', id);
    popup.innerHTML = footNote;
    e.target.parentNode.insertBefore(popup, e.target.nextSibling);
    Popper.createPopper(e.target, popup, {
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [0, 8],
          },
        },
      ],
    });
  })
}(drupalSettings, Popper))
