import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import TextEditor from 'flarum/common/components/TextEditor';
import CheveretoButton from './components/CheveretoButton';

let isListening = false;
function startListen() {
  window.addEventListener('message', function (e) {
    if (e.data.id) {
      if (e.data.requestAction == 'postSettings') {
        e.source.postMessage(
          {
            id: e.data.id,
            settings: { autoInsert: app.forum.attribute('akr-chevereto.insert_type') },
          },
          e.origin
        );
      }
      if (e.data.message && app.composer.editor) {
        app.composer.editor.insertAtCursor(e.data.message);
      }
    }
  });
  isListening = true;
}

app.initializers.add('akr-chevereto', (app) => {
  extend(TextEditor.prototype, 'oncreate', function () {
    if (app.forum.attribute('akr-chevereto.url') && !isListening) {
      startListen();
    }
  });

  extend(TextEditor.prototype, 'controlItems', function (items) {
    if (app.forum.attribute('akr-chevereto.url')) {
      items.add('akr-chevereto', CheveretoButton.component());
    }
  });
});
