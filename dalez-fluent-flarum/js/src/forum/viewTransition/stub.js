/*
 *  This file is part of dalez/fluent-flarum
 *
 *  Copyright (c) 2025 DaleZ.
 *
 *  For detailed copyright and license information, please view the
 *  LICENSE-SCRIPT file that was distributed with this source code.
 */

/*
    The code below isn't used here; It'll be appended to
    <head> as a part of code of patching mithril. However,
    we keep the original code for clearity.
*/

(() => {
  if (!document.startViewTransition) return;
  window.rAF = requestAnimationFrame;
  window.requestAnimationFrame = (func) => {
    if (!window.flarum || typeof flarum.fluent_internal_transition_controller != 'function') {
      window.rAF(func);
      return;
    }

    flarum.fluent_internal_transition_controller(func);
  };
})();
