import app from 'flarum/forum/app';
import type Language from '../../common/models/Language';

/**
 * The composer's fields are typed as streams, but extensions store plain values in them.
 */
export default function composerFields(): { language?: Language | '' | null } {
  return app.composer.fields as unknown as { language?: Language | '' | null };
}
