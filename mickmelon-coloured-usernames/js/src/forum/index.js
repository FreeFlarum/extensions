import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import PostUser from 'flarum/forum/components/PostUser';

app.initializers.add('mickmelon-coloured-usernames', () => {
  extend(PostUser.prototype, 'linkChildren', function (items, user) {
    // Find the first group that has a color
    // We don't read badges because we would need to support every badge component and its attrs
    const firstColoredGroup = user.groups().find((group) => {
      return group.color();
    });

    // If there are no color groups, skip
    if (!firstColoredGroup || !items.has('username')) {
      return;
    }

    // Set the colour of the username class
    const username = items.get('username');

    username.attrs = username.attrs || {};
    username.attrs.style = username.attrs.style || {};
    username.attrs.style.color = firstColoredGroup.color();
  });
});
