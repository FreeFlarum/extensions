/*
 * This file is part of justoverclock/edit-posts.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 * based on Clark Winkelmann tutorial
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import CommentPost from 'flarum/forum/components/CommentPost';
import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import PostControls from 'flarum/forum/utils/PostControls';

app.initializers.add('justoverclock/edit-posts', () => {
  extend(CommentPost.prototype, 'content', function (content) {
    if (!this.attrs.post.canEdit()) {
      return;
    }
    if (this.attrs.post.isHidden()) {
      return;
    }
    content.push(
      Button.component({
        className: 'Dropdown-toggle Button Button--icon Button--flat hidebutton',
        icon: 'fas fa-eye',
        title: app.translator.trans('justoverclock-edit-posts.forum.hidepost'),
        onclick: PostControls.hideAction.bind(this.attrs.post),
      })
    );
    content.push(
      Button.component({
        className: 'Dropdown-toggle Button Button--icon Button--flat edipostbutton',
        icon: 'fas fa-pen',
        title: app.translator.trans('justoverclock-edit-posts.forum.editpost'),
        onclick: PostControls.editAction.bind(this.attrs.post),
      })
    );
    content.push(
      Button.component({
        className: 'Dropdown-toggle Button Button--icon Button--flat delpostbutton',
        icon: 'far fa-trash-alt',
        title: app.translator.trans('justoverclock-edit-posts.forum.deleteforever'),
        onclick: PostControls.deleteAction.bind(this.attrs.post),
      })
    );
  });
  extend(PostControls, 'moderationControls', function (items) {
    if (items.has('edit')) {
      items.remove('edit');
    }
  });
  extend(PostControls, 'destructiveControls', function (items) {
    if (items.has('hide')) {
      items.remove('hide');
    }
    if (items.has('delete')) {
      items.remove('delete');
    }
  });
});
