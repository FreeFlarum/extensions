import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import type DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

import LanguageDiscussionModal from './components/LanguageDiscussionModal';
import LanguageDisplay from './components/LanguageDisplay';
import composerFields from './utils/composerFields';
import currentLanguage from './utils/currentLanguage';
import type Language from '../common/models/Language';

const sort = (a: Language, b: Language) => (a.code()!.toLowerCase() > b.code()!.toLowerCase() ? 1 : -1);

export default () => {
  extend(IndexSidebar.prototype, 'newDiscussionAction', function (promise: Promise<unknown>) {
    const dislang = currentLanguage();

    if (dislang) {
      promise.then(
        () => (composerFields().language = app.store.getBy<Language>('discussion-languages', 'code', dislang)),
        () => {}
      );
    } else {
      const localeComposer = app.forum.attribute('fof-discussion-language.composerLocaleDefault');
      composerFields().language = localeComposer ? app.store.getBy<Language>('discussion-languages', 'code', app.translator.getLocale()) : '';
    }
  });

  extend('flarum/forum/components/DiscussionComposer', 'oninit', function (this: DiscussionComposer) {
    (this.constructor as typeof DiscussionComposer).prototype.chooseLanguage = function (
      this: DiscussionComposer,
      hide: boolean,
      callback: (() => void) | null
    ) {
      app.modal.show(LanguageDiscussionModal, {
        selected: composerFields().language || undefined,
        hideSubmitButton: hide,
        onsubmit: (language: Language) => {
          composerFields().language = language;
          this.$('textarea').focus();

          if (callback) callback();
        },
      });
    };
  });

  extend('flarum/forum/components/DiscussionComposer', 'headerItems', function (this: DiscussionComposer, items: ItemList<Mithril.Children>) {
    // Don't add lang item to byobu composers
    if (this._isByobuComposer) return;

    const language = composerFields().language;

    items.add(
      'language',
      <a className="DiscussionComposer-changeTags" onclick={this.chooseLanguage.bind(this, true, null)}>
        <span className={`LanguageLabel ${language ? '' : 'none'}`}>
          {language ? (
            <LanguageDisplay language={language} uppercase={true} />
          ) : (
            app.translator.trans('fof-discussion-language.forum.composer_discussion.choose_language_link')
          )}
        </span>
      </a>,
      20
    );
  });

  override('flarum/forum/components/DiscussionComposer', 'onsubmit', function (this: DiscussionComposer, original: () => void) {
    // Don't add lang stuff to byobu composers
    if (this._isByobuComposer) return original();

    if (!composerFields().language) return this.chooseLanguage(true, original);

    original();
  });

  extend('flarum/forum/components/DiscussionComposer', 'data', function (this: DiscussionComposer, data: Record<string, unknown>) {
    // Don't add lang stuff to byobu composers
    if (this._isByobuComposer) return;

    data.relationships = (data.relationships as Record<string, unknown>) || {};

    (data.relationships as Record<string, unknown>).language =
      composerFields().language || app.store.all<Language>('discussion-languages').sort(sort)[0];
  });
};
