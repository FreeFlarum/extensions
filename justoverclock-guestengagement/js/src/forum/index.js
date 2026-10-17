/*
 * This file is part of justoverclock/flarum-ext-guestengagement.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/forum/app';

import { override } from 'flarum/common/extend';

import IndexPage from 'flarum/forum/components/IndexPage';
import Button from 'flarum/common/components/Button';

app.initializers.add('justoverclock/flarum-ext-guestengagement', () => {
  override(IndexPage.prototype, 'hero', function (original) {
    if (app.session.user || app.forum.attribute('justoverclock-guestengagement.hideHomeBox') !== true) {
      return original();
    }

    /*  Imposta un timeout per far scomparire automaticamente il div*/
    setTimeout(function () {
      $('#wrapperengage').fadeOut().empty();
    }, app.forum.attribute('justoverclock-guestengagement.timeOut'));
    const insert = m(
      'div',
      { id: 'wrapperengage' },
      m('div', { id: 'engagebox' }, [
        m('p', [
          m('strong', app.translator.trans('flarum-ext-guestengagement.forum.hello')),
          m('p'),
          app.translator.trans('flarum-ext-guestengagement.forum.whenucreate'),
          app.translator.trans('flarum-ext-guestengagement.forum.uwillreceive'),
        ]),
        m(
          'button',
          {
            className: '.SplitDropdown-button Button Button--primary hasIcon',
            type: 'button',
            onclick: () => app.modal.show(() => import('flarum/forum/components/SignUpModal')),
          },
          app.translator.trans('core.forum.header.sign_up_link')
        ),
      ])
    );

    return [original(), insert];
  });

  override('flarum/forum/components/PostStream', 'view', function (originalView) {
    // If we're logged in or this feature is disabled, change nothing
    // about the PostStream
    if (app.session.user || app.forum.attribute('justoverclock-guestengagement.hidePostBox') !== true) {
      return originalView();
    }

    /**
     * Array of items inside the PostStream.
     *
     * @type {import('mithril').Children[]}
     */
    const vdom = originalView();
    const postStreamItems = vdom.children;

    const engagementBox = (
      <div key="justoverclock-guestengagement" id="wrapperengageps">
        <div id="engageboxps">
          <p>
            <strong>{app.forum.attribute('justoverclock-guestengagement.BoxTitle')}</strong>
          </p>
          <p>{app.forum.attribute('justoverclock-guestengagement.tchange')}</p>
          <Button class="Button Button--primary" onclick={() => app.modal.show(() => import('flarum/forum/components/SignUpModal'))}>
            {app.translator.trans('flarum-ext-guestengagement.forum.signupbtn')}
          </Button>
        </div>
      </div>
    );

    const newItems = postStreamItems.reduce((items, currentItem) => {
      let itemList = [...items, currentItem];

      if (currentItem?.attrs?.['data-index'] === (parseInt(app.forum.attribute('justoverclock-guestengagement.xPost')) || 0)) {
        itemList.push(engagementBox);
      }

      return itemList;
    }, []);

    vdom.children = newItems;

    return vdom;
  });
});
