/*
 * This file is part of MathRen.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Native Flarum composer components. They are lazy loaded,
 * so they are referenced by their module paths.
 */
export const flarumComposerPaths = [
  'flarum/forum/components/DiscussionComposer',
  'flarum/forum/components/EditPostComposer',
  'flarum/forum/components/ReplyComposer',
];

/**
 * Returns a list of the loaded native Flarum composer components.
 * This list will be used to determine if a composer
 * defined by a third-party extension.
 *
 * @return { Array }
 */
export default function getFlarumComposers() {
  return flarumComposerPaths.map((path) => flarum.reg.checkModule(...flarum.reg.namespaceAndIdFromPath(path))).filter(Boolean);
}
