import Tag from 'ext:flarum/tags/common/models/Tag';
import Discussion from 'flarum/common/models/Discussion';
import Forum from 'flarum/common/models/Forum';
import Language from '../common/models/Language';

declare module 'ext:flarum/tags/common/models/Tag' {
  export default interface Tag {
    localisedLastDiscussion(): Record<string, { title: string; id: string; at: number }>;
    subscriptionLanguage(): string | null;
  }
}

declare module 'flarum/common/models/Discussion' {
  export default interface Discussion {
    language(): Language | false | null;
    canChangeLanguage(): boolean | undefined;
    isPrivateDiscussion?(): boolean;
  }
}

declare module 'flarum/common/models/Forum' {
  export default interface Forum {
    discussionLanguages(): Language[] | false;
  }
}

declare module 'flarum/forum/components/DiscussionComposer' {
  export default interface DiscussionComposer {
    chooseLanguage(hide: boolean, callback: (() => void) | null): void;
    _isByobuComposer?: boolean;
  }
}
