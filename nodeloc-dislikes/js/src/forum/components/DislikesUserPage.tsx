import PostsUserPage from 'flarum/forum/components/PostsUserPage';
import type User from 'flarum/common/models/User';

/**
 * The `DislikesUserPage` component shows posts which user the user disliked.
 */
export default class DislikesUserPage extends PostsUserPage {
  params(user: User) {
    return {
      filter: {
        type: 'comment',
        dislikedBy: user.id(),
      },
    };
  }
}
