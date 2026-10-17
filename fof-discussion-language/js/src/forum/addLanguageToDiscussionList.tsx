import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionHero from 'flarum/forum/components/DiscussionHero';
import DiscussionListState from 'flarum/forum/states/DiscussionListState';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import Icon from 'flarum/common/components/Icon';
import setRouteWithForcedRefresh from 'flarum/common/utils/setRouteWithForcedRefresh';
import type ItemList from 'flarum/common/utils/ItemList';
import type { PaginatedListRequestParams } from 'flarum/common/states/PaginatedListState';
import type Mithril from 'mithril';

import flag from '../common/utils/flag';
import LanguageDropdown from './components/LanguageDropdown';
import currentLanguage from './utils/currentLanguage';

const addLanguage = function (this: DiscussionListItem | DiscussionHero, items: ItemList<Mithril.Children>) {
  // Ignore PDs from Byobu
  // Must be conditional call in case byobu not installed
  if (this.attrs.discussion.isPrivateDiscussion?.()) return;

  const language = this.attrs.discussion.language?.();

  if (!language) return;

  items.add(
    'discussion-language',
    <span>
      {flag(language) || <Icon name="fas fa-globe" />}
      <code>{language.name()}</code>
    </span>,
    5
  );
};

export default () => {
  extend(DiscussionListItem.prototype, 'infoItems', addLanguage);
  extend(DiscussionHero.prototype, 'items', addLanguage);

  extend(DiscussionListState.prototype, 'requestParams', function (params: PaginatedListRequestParams) {
    const routeName = app.current.get('routeName');

    if (routeName === 'byobuPrivate') return;
    if (routeName === 'byobuUserPrivate') return;

    (params.include as string[]).push('language');

    // Do not filter user discussions page as there is no dropdown
    if (routeName === 'user.discussions') return;

    const selectedLang = currentLanguage();

    // 'any' means no filter — show all languages.
    if (selectedLang === 'any') return;

    if (selectedLang || !app.forum.attribute('fof-discussion-language.showAnyLangOpt')) {
      params.filter = { ...params.filter, language: selectedLang ?? app.translator.getLocale() };
    }
  });

  extend(IndexPage.prototype, 'viewItems', function (items: ItemList<Mithril.Children>) {
    // Don't add language controls to /private (fof/byobu)
    if (app.current.get('routeName') === 'byobuPrivate') return;

    const defaultSelected = app.forum.attribute('fof-discussion-language.showAnyLangOpt') ? 'any' : app.translator.getLocale();

    items.add(
      'language',
      <LanguageDropdown
        selected={currentLanguage() ?? defaultSelected}
        onclick={(key: string) => {
          const params = app.search.state.params();
          const filter = (params.filter as unknown as Record<string, string>) ?? {};

          if (key === defaultSelected) {
            delete filter.language;
          } else {
            filter.language = key;
          }

          params.filter = filter as unknown as string;

          setRouteWithForcedRefresh(app.route(app.current.get('routeName'), params));
        }}
      />
    );
  });
};
