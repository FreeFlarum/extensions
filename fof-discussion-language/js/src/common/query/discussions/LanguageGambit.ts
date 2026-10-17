import app from 'flarum/common/app';
import { KeyValueGambit } from 'flarum/common/query/IGambit';

export default class LanguageGambit extends KeyValueGambit {
  key(): string {
    return app.translator.trans('fof-discussion-language.lib.gambits.discussions.language.key', {}, true);
  }

  hint(): string {
    return app.translator.trans('fof-discussion-language.lib.gambits.discussions.language.hint', {}, true);
  }

  filterKey(): string {
    return 'language';
  }
}
