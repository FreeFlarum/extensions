import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import humanTime from 'flarum/common/utils/humanTime';
import extractText from 'flarum/common/utils/extractText';
import ItemList from 'flarum/common/utils/ItemList';
import Avatar from 'flarum/common/components/Avatar';
import Icon from 'flarum/common/components/Icon';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import Tooltip from 'flarum/common/components/Tooltip';
import Model from 'flarum/common/Model';
import Discussion from 'flarum/common/models/Discussion';
import Post from 'flarum/common/models/Post';
import Forum from 'flarum/common/models/Forum';
import User from 'flarum/common/models/User';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import PostControls from 'flarum/forum/utils/PostControls';
import CommentPost from 'flarum/forum/components/CommentPost';
import PostUser from 'flarum/forum/components/PostUser';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import TerminalPost from 'flarum/forum/components/TerminalPost';
import PostPreview from 'flarum/forum/components/PostPreview';
import PostsUserPage from 'flarum/forum/components/PostsUserPage';

function extendComposerInit() {
  app.composer.fields!.isAnonymous = !!app.forum.attribute('defaultAnonymousPost');
}

function extendComposerHeaderItems(items: ItemList<any>) {
  if (!app.forum.attribute('canAnonymousSwitch')) {
    return;
  }

  const helpText = app.translator.trans('clarkwinkelmann-anonymous-posting.forum.composerControls.anonymizeHelp');
  const helpTextPosition = app.forum.attribute('anonymousHelpTextPosition');

  items.add(
    'anonymous-posting',
    Switch.component(
      {
        className: helpTextPosition === 'visible' ? 'AnonymousCheckbox--multiline' : '',
        state: !!app.composer.fields!.isAnonymous,
        onchange: (value: boolean) => {
          app.composer.fields!.isAnonymous = value;
        },
      },
      m('span.AnonymousCheckboxLabel', [
        app.translator.trans('clarkwinkelmann-anonymous-posting.forum.composerControls.anonymize'),
        helpTextPosition === 'tooltip'
          ? [
              ' ',
              Tooltip.component(
                {
                  text: helpText,
                },
                Icon.component({
                  name: 'fas fa-info-circle',
                  className: 'AnonymousCheckboxInfo',
                })
              ),
            ]
          : null,
        helpTextPosition === 'visible' ? m('.helpText', helpText) : null,
      ])
    ),
    -10
  );
}

function extendComposerData(data: any) {
  data.isAnonymous = !!app.composer.fields!.isAnonymous;
}

function extendComposerView(vdom: any) {
  if (!app.composer.fields!.isAnonymous) {
    return;
  }

  if (!vdom || !Array.isArray(vdom.children)) {
    return;
  }

  // Loop through <ConfirmDocumentUnload> children
  vdom.children.forEach((vdom: any) => {
    if (!vdom || !Array.isArray(vdom.children)) {
      return;
    }

    // Loop through .ComposerBody children
    vdom.children.forEach((child: any, index: number) => {
      if (!child || !child.attrs || !child.attrs.className || child.attrs.className.indexOf('ComposerBody-avatar') === -1) {
        return;
      }

      vdom.children[index] = anonymousAvatar(app.forum, '.ComposerBody-avatar');
    });
  });
}

function anonymousAvatar(post: Discussion | Post | Forum, className: string = '') {
  const src = post.attribute('anonymousAvatarUrl');

  if (src) {
    return m('img.Avatar.Avatar--anonymous' + className, {
      src,
      alt: app.translator.trans('clarkwinkelmann-anonymous-posting.lib.userMeta.username'),
    });
  }

  return m('span.Avatar.Avatar--anonymous' + className, app.translator.trans('clarkwinkelmann-anonymous-posting.lib.userMeta.initials'));
}

function anonymousUsername() {
  return m('span.username', app.translator.trans('clarkwinkelmann-anonymous-posting.lib.userMeta.username'));
}

function hasClass(vdom: any, className: string): boolean {
  return !!(vdom && vdom.attrs && typeof vdom.attrs.className === 'string' && vdom.attrs.className.split(' ').indexOf(className) !== -1);
}

// Replaces the actor's avatar, name and badges in the reply preview
function anonymizeReplyPreview(vdom: any) {
  if (!vdom || !Array.isArray(vdom.children)) {
    return;
  }

  for (let index = vdom.children.length - 1; index >= 0; index--) {
    const child = vdom.children[index];

    if (child && child.tag === Avatar) {
      vdom.children[index] = anonymousAvatar(app.forum, '.Post-avatar');
    } else if (hasClass(child, 'PostUser-name')) {
      child.children = [anonymousUsername()];
    } else if (hasClass(child, 'PostUser-badges')) {
      vdom.children.splice(index, 1);
    } else {
      anonymizeReplyPreview(child);
    }
  }
}

app.initializers.add('anonymous-posting', () => {
  override(CommentPost.prototype, 'avatar', function (original) {
    const { post } = this.attrs;

    if (!post.attribute('isAnonymous')) {
      return original();
    }

    return anonymousAvatar(post, '.Post-avatar');
  });

  extend(CommentPost.prototype, 'headerItems', function (items) {
    const { post } = this.attrs;

    if (!post.attribute('isAnonymous')) {
      return;
    }

    items.setContent('user', m('.PostUser', m('h3.PostUser-name', [anonymousAvatar(post, '.Post-avatar'), ' ', anonymousUsername()])));

    const anonymousUser = Model.hasOne<User>('anonymousUser').call(post);

    if (!anonymousUser) {
      if (!post.attribute('isAnonymousMe')) {
        return;
      }

      let className = '.AnonymousPostPrivacyMine';
      let tooltipText = app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postPrivacy.mineHelp');
      let labelText = app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postPrivacy.mine');

      if (app.current.matches(PostsUserPage)) {
        className = '.AnonymousPostPrivacyProfile';
        tooltipText = app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postPrivacy.profileHelp');
        labelText = app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postPrivacy.profile');
      }

      items.add(
        'anonymousUserPrivacy',
        Tooltip.component(
          {
            text: tooltipText,
          },
          m('span.AnonymousPostPrivacy' + className, labelText)
        ),
        90 // Just after the original user label
      );

      return;
    }

    // Provide an altered post object that the PostUser component can read the user from
    const alteredPost = new Post({
      ...post.data,
      relationships: {
        ...post.data.relationships,
        user: post.data.relationships!.anonymousUser,
      },
    });

    items.add(
      'anonymousUser',
      PostUser.component({
        post: alteredPost,
      }),
      90 // Just after the original user label
    );
  });

  extend('flarum/forum/components/ReplyPlaceholder', 'view', function (vdom) {
    if (!app.composer.fields!.isAnonymous || !app.composer.composingReplyTo(this.attrs.discussion as Discussion)) {
      return;
    }

    anonymizeReplyPreview(vdom);
  });

  extend(DiscussionListItem.prototype, 'authorItems', function (items) {
    const discussion = this.attrs.discussion;

    if (!discussion.attribute('isAnonymous') || !items.has('avatar')) {
      return;
    }

    items.setContent(
      'avatar',
      Tooltip.component(
        {
          text: app.translator.trans('core.forum.discussion_list.started_text', {
            username: app.translator.trans('clarkwinkelmann-anonymous-posting.lib.userMeta.username'),
            ago: humanTime(discussion.createdAt()),
          }),
          position: 'right',
        },
        m('span.DiscussionListItem-author-avatar', anonymousAvatar(discussion))
      )
    );
  });

  extend(PostPreview.prototype, 'view', function (vdom) {
    if (!this.attrs.post.attribute('isAnonymous')) {
      return;
    }

    vdom.children.forEach((preview: any) => {
      if (!hasClass(preview, 'PostPreview-content')) {
        return;
      }

      preview.children.forEach((child: any, index: number) => {
        if (child && child.tag === Avatar) {
          preview.children[index] = anonymousAvatar(this.attrs.post);
        } else if (hasClass(child, 'username')) {
          preview.children[index] = anonymousUsername();
        }
      });
    });
  });

  override(TerminalPost.prototype, 'view', function (original, ...args) {
    const discussion = this.attrs.discussion;

    if (this.attrs.lastPost && discussion.replyCount()) {
      if (discussion.lastPostedUser()) {
        return original(...args);
      }

      // Loading discussion.lastPost for the sole purpose of getting post.isAnonymous would be detrimental to performance
      // Instead we'll replace all "[deleted] replied" texts with a generic message that doesn't include a username

      return m('span', [
        Icon.component({
          name: 'fas fa-reply',
        }),
        ' ',
        app.translator.trans('clarkwinkelmann-anonymous-posting.forum.discussionList.genericReplyText', {
          ago: humanTime(discussion.lastPostedAt()),
        }),
      ]);
    }

    if (!discussion.attribute('isAnonymous')) {
      return original(...args);
    }

    return m('span', [
      '', // Keep output the same as original method to maximise compatibility with other extensions
      ' ',
      app.translator.trans('core.forum.discussion_list.started_text', {
        username: anonymousUsername(),
        ago: humanTime(discussion.createdAt()),
      }),
    ]);
  });

  extend(CommentPost.prototype, 'oninit', function () {
    this.subtree!.check(() => {
      return this.attrs.post.attribute('isAnonymous');
    });
  });

  ['flarum/forum/components/DiscussionComposer', 'flarum/forum/components/ReplyComposer'].forEach((composer) => {
    extend(composer, 'oninit', extendComposerInit);
    extend(composer, 'headerItems', extendComposerHeaderItems);
    extend(composer, 'data', extendComposerData);
    extend(composer, 'view', extendComposerView);
  });

  extend(DiscussionControls, 'moderationControls', function (items, discussion) {
    if (discussion.attribute('canDeAnonymize')) {
      items.add(
        'deanonymize',
        Button.component(
          {
            icon: 'fas fa-user-secret',
            onclick: () => {
              if (!confirm(extractText(app.translator.trans('clarkwinkelmann-anonymous-posting.forum.discussionControls.deanonymizeConfirmation')))) {
                return;
              }

              discussion
                .save({
                  isAnonymous: false,
                })
                .then(() => {
                  m.redraw();
                });
            },
          },
          app.translator.trans('clarkwinkelmann-anonymous-posting.forum.discussionControls.deanonymize')
        )
      );
    }

    if (discussion.attribute('canAnonymize')) {
      items.add(
        'anonymize',
        Button.component(
          {
            icon: 'fas fa-user-secret',
            onclick: () => {
              if (!confirm(extractText(app.translator.trans('clarkwinkelmann-anonymous-posting.forum.discussionControls.anonymizeConfirmation')))) {
                return;
              }

              discussion
                .save({
                  isAnonymous: true,
                })
                .then(() => {
                  m.redraw();
                });
            },
          },
          app.translator.trans('clarkwinkelmann-anonymous-posting.forum.discussionControls.anonymize')
        )
      );
    }
  });

  extend(PostControls, 'moderationControls', function (items, post) {
    if (post.attribute('canDeAnonymize')) {
      items.add(
        'deanonymize',
        Button.component(
          {
            icon: 'fas fa-user-secret',
            onclick: () => {
              if (!confirm(extractText(app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postControls.deanonymizeConfirmation')))) {
                return;
              }

              post
                .save({
                  isAnonymous: false,
                })
                .then(() => {
                  m.redraw();
                });
            },
          },
          app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postControls.deanonymize')
        )
      );
    }

    if (post.attribute('canAnonymize')) {
      items.add(
        'anonymize',
        Button.component(
          {
            icon: 'fas fa-user-secret',
            onclick: () => {
              if (post.number() === 1) {
                if (!confirm(extractText(app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postControls.firstPostConfirmation')))) {
                  return;
                }
              }

              post
                .save({
                  isAnonymous: true,
                })
                .then(() => {
                  m.redraw();
                });
            },
          },
          app.translator.trans('clarkwinkelmann-anonymous-posting.forum.postControls.anonymize')
        )
      );
    }
  });

  // Flarum will try to focus the checkbox in ReplyComposer instead of the body. To work around this, we'll temporarily disable the field while this method runs
  override('flarum/forum/components/Composer', 'focus', function (original) {
    const $anonymousCheckbox = this.$('.item-anonymous-posting input');

    $anonymousCheckbox.prop('disabled', true);

    const returnValue = original();

    $anonymousCheckbox.prop('disabled', false);

    return returnValue;
  });
});
