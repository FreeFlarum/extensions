/**
 * Read the currently selected language from the URL's filter param.
 * Falls back to the legacy top-level ?language param for backwards compat.
 */
export default function currentLanguage(): string | undefined {
  const filter = m.route.param('filter') as Record<string, string> | undefined;

  return filter?.language ?? m.route.param('language');
}
