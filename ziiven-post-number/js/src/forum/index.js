import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import CommentPost from 'flarum/forum/components/CommentPost';

app.initializers.add('post-number', () => {
  extend(CommentPost.prototype, 'headerItems', function (items) {
    const postNumberText = app.translator.trans('post-number.forum.number-prefix', { number: this.attrs.post.number() });
    const postIsHidden = this.attrs.post.isHidden();

    if (postIsHidden !== true) {
      items.add('post-number', <span className="postNumber">{postNumberText}</span>);
    }
  });
});
