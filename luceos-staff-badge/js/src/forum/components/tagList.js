import Component from 'flarum/common/Component';

export default class TagList extends Component {
    view() {
        const user = this.attrs.user;

        let tags = ``;
        let tagList = user.tagList().split(",");

        if(tagList.length > 0) {
            for(let tag of tagList) {
                tags += `<span class="userTag">${tag}</span>`;
            }
        }

        return (
            <div className="userTags">
                {m.trust(tags)}
            </div>
        );
    }
}
