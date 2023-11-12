(function (Drupal, Editor) {

  Drupal.EditorJs = {
    getHolderElement(target) {
      let holder = target.parentNode.querySelector('.editorjs_holder');

      if (!holder) {
        holder = document.createElement('div');
        holder.classList.add('editorjs_holder');
        target.parentNode.insertBefore(holder, target.nextSibling);
      }

      return holder;
    },
    prepareTools: function (tools) {
      Object.keys(tools).map(tool => {
        if (tools[tool].hasOwnProperty('class') && tools[tool].class) {
          tools[tool].class = window[tools[tool].class]
        }
      })
      return tools;
    },
  }

  Drupal.behaviors.EditorJsInit = {
    attach: function (context, settings) {
      context = context || document;
      settings = settings.editorjs || {};
      let items = context.querySelectorAll('.editorjs');
      items.forEach(item => {
        let data = {};
        if (item.value) {
          data['blocks'] = JSON.parse(item.value);
        }
        let ei = new Editor({
          holder: Drupal.EditorJs.getHolderElement(item),
          tools: Drupal.EditorJs.prepareTools(settings[item.id].tools || {}),
          data: data,
          onChange: function () {
            ei.save().then((data) => {
              item.value = JSON.stringify(data.blocks)
            }).catch((error) => {
              console.log('Saving failed: ', error)
            });
          }
        });
      })
    }
  }
}(Drupal, EditorJS))
