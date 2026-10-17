import Extend from 'flarum/common/extenders';
import BookmarkedGambit from './query/discussions/BookmarkedGambit';

export default [new Extend.Search().gambit('discussions', BookmarkedGambit)];
