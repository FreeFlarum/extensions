import {ClassComponent, Vnode} from 'mithril';
import app from 'flarum/forum/app';
import Link from 'flarum/common/components/Link';
import Avatar from 'flarum/common/components/Avatar';
import username from 'flarum/common/helpers/username';
import UserModel from 'flarum/common/models/User';

interface UserAttrs {
    user: UserModel
}

export default class User implements ClassComponent<UserAttrs> {
    view(vnode: Vnode<UserAttrs, this>) {
        const {user} = vnode.attrs;

        return m(Link, {
            href: app.route.user(user),
        }, [
            m(Avatar, {user}),
            ' ',
            username(user),
        ]);
    }
}
