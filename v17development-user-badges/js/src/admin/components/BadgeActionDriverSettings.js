import Component from 'flarum/common/Component';
import BadgeSelector from './BadgeSelector';

export default class BadgeActionDriverSettings extends Component {
  view() {
    const settings = this.attrs.settings;

    return <BadgeSelector value={settings().badge_id} onchange={(val) => settings({ badge_id: val })} />;
  }
}
