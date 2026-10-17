import app from 'flarum/forum/app';

import Tag from 'ext:flarum/tags/common/models/Tag';

import Discussion from 'flarum/common/models/Discussion';
import Model from 'flarum/common/Model';

export default function useLocalisedLastDiscussionOnTagsPage() {
  if (!('flarum-tags' in flarum.extensions)) return;

  Tag.prototype.lastPostedDiscussion = function (this: Tag) {
    const original = Model.hasOne<Discussion>('lastPostedDiscussion').bind(this);

    if (!app.forum.attribute('fof-discussion-language.useLocaleForTagsPageLastDiscussion')) {
      return original();
    }

    const data = this.localisedLastDiscussion();
    const languages = app.forum.discussionLanguages() || [];

    /**
     * Priorities:
     *
     * 1. User's selected forum locale
     * 2. Detected locale
     * 3. First disucssion language available
     */
    const desiredLanguageCode = app.session.user?.preferences?.()?.locale || app.translator.getLocale() || languages[0]?.code();

    const desiredLanguageId = languages.find((lang) => lang.code() === desiredLanguageCode)?.id();

    // If forum locale not available as a discussion lang, default to any language
    if (!desiredLanguageId) {
      return original();
    }

    const discussion = data?.[desiredLanguageId];

    // fall back to original attribute
    if (!discussion) return original();

    return new Discussion({
      attributes: {
        title: discussion.title,
        slug: discussion.id, // navigating using only ID works fine
        lastPostedAt: new Date(discussion.at * 1000).toISOString(),
      },
    });
  };
}
