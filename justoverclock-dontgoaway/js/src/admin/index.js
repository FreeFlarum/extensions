/*
 * This file is part of justoverclock/flarum-ext-guestengagement.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/admin/app';

app.initializers.add('justoverclock/flarum-ext-dontgoaway', () => {
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.modaltitle',
    name: 'ModalTitle',
    type: 'text',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.modtitle'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.modtitledesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.ModalText',
    name: 'ModalText',
    type: 'text',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.modtext'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.modtextdesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.UrlImage',
    name: 'UrlImage',
    type: 'url',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.urltext'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.urltextdesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.WidthImg',
    name: 'WidthImg',
    type: 'number',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.widthtext'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.widthdesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.HeightImg',
    name: 'HeightImg',
    type: 'number',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.heightext'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.heightdesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.AltAttr',
    name: 'AltAttr',
    type: 'text',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.altext'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.altdesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.ModalBtn',
    name: 'ModalBtn',
    type: 'text',
    placeholder: '<i class="fa fa-thumbs-up"></i> Ok, just 5 minutes!',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.btn'),
    help: app.translator.trans('flarum-ext-dontgoaway.admin.btndesc'),
  });
  app.registry.for('justoverclock-dontgoaway').registerSetting({
    setting: 'justoverclock-dontgoaway.enable.extlink',
    label: app.translator.trans('flarum-ext-dontgoaway.admin.enableExtLink'),
    type: 'boolean',
  });
});
