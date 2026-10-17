import Mithril from 'mithril';
import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Avatar, { IAvatarAttrs } from 'flarum/common/components/Avatar';
import ItemList from 'flarum/common/utils/ItemList';
import AvatarEditor from 'flarum/forum/components/AvatarEditor';
import CommentPost from 'flarum/forum/components/CommentPost';
import PostUser from 'flarum/forum/components/PostUser';
import UserCard from 'flarum/forum/components/UserCard';

type StyledVnode = Mithril.Vnode<{ style?: Record<string, string> }>;

// Avatar is a component in Flarum 2, the color is applied to the element it renders
class CircleGroupsAvatar extends Avatar {
  view(vnode: Mithril.Vnode<IAvatarAttrs, this>) {
    const vdom = super.view(vnode) as StyledVnode;
    const user = vnode.attrs.user;

    const groups = user ? user.groups() : false;

    // If a user has no groups or groups have not been loaded, false might be returned
    // We'll skip this part if this happens
    if (!Array.isArray(groups)) {
      return vdom;
    }

    // Find the first group that has a color
    // We don't read badges because we would need to support every badge component and its attrs
    const firstColoredGroup = groups.find((group) => {
      return group && group.color();
    });

    // If there are no color groups, skip
    if (!firstColoredGroup) {
      return vdom;
    }

    vdom.attrs.style = vdom.attrs.style || {};
    vdom.attrs.style.borderColor = firstColoredGroup.color();

    return vdom;
  }
}

function findAvatar(node: Mithril.Children): Mithril.Vnode | null {
  if (!node || typeof node !== 'object') {
    return null;
  }

  if (Array.isArray(node)) {
    for (const child of node) {
      const avatar = findAvatar(child);

      if (avatar) {
        return avatar;
      }
    }

    return null;
  }

  const vnode = node as Mithril.Vnode;

  if (vnode.tag === Avatar) {
    return vnode;
  }

  return findAvatar(vnode.children);
}

function colorAvatar(node: Mithril.Children): void {
  const avatar = findAvatar(node);

  if (avatar) {
    avatar.tag = CircleGroupsAvatar;
  }
}

app.initializers.add('clarkwinkelmann-circle-groups', () => {
  extend(CommentPost.prototype, 'avatar', function (vdom: Mithril.Children) {
    colorAvatar(vdom);
  });

  extend(PostUser.prototype, 'linkChildren', function (items: ItemList<Mithril.Children>) {
    if (items.has('avatar')) {
      colorAvatar(items.get('avatar'));
    }
  });

  // Only contains an Avatar outside of edit mode, the AvatarEditor is handled below
  extend(UserCard.prototype, 'avatar', function (vdom: Mithril.Children) {
    colorAvatar(vdom);
  });

  // We color the avatar editor because it's what users see on their own profile, or admins everywhere
  extend(AvatarEditor.prototype, 'view', function (vdom: Mithril.Children) {
    colorAvatar(vdom);
  });
});
