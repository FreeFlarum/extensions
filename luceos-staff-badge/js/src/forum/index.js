import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import extractText from 'flarum/common/utils/extractText';
import Stream from 'flarum/common/utils/Stream';
import UserCard from 'flarum/forum/components/UserCard';
import PostUser from 'flarum/forum/components/PostUser';
import TagList from './components/tagList';

export { default as extend } from './extend';

app.initializers.add('luceos-staff-badge', () => {
    extend(PostUser.prototype, 'oncreate', function (_out,vnode) {
        const user = this.attrs.post.user();

        if(!user) return;
        const badge = user.staffBadge();
        if(!badge) return;
        if(badge.toLowerCase() !== "true") return;

        let staffBadgeText = app.forum.attribute('staffBadgeTitle')?.toString();
        let staffBadgeColor = app.forum.attribute('staffBadgeColor')?.toString();
        let staffBadgeBg = app.forum.attribute('staffBadgeBg')?.toString();
        if(!staffBadgeText) staffBadgeText = "STAFF";
        if(!staffBadgeBg) staffBadgeBg = "rgb(70, 209, 96)";
        if(!staffBadgeColor) staffBadgeColor = "#fff";

        const anchor = vnode.dom;

        if(!anchor) return;
        const newEl = document.createElement('div');
        newEl.className = 'badgeHolder';
        newEl.innerHTML = `<div style="background-color:${staffBadgeBg};color:${staffBadgeColor}" class="ext_staffbadge_sm">
            ${staffBadgeText}
        </div>`;

        anchor.appendChild(newEl);
    });

    extend(UserCard.prototype, 'oncreate', function(_out, vnode) {
        const staffBadge = this.attrs.user.staffBadge();
        if(staffBadge){
            if(staffBadge.toLowerCase() === "true"){
                let staffBadgeText = app.forum.attribute('staffBadgeTitle')?.toString();
                let staffBadgeColor = app.forum.attribute('staffBadgeColor')?.toString();
                let staffBadgeBg = app.forum.attribute('staffBadgeBg')?.toString();
                if(!staffBadgeText) staffBadgeText = "STAFF";
                if(!staffBadgeBg) staffBadgeBg = "rgb(70, 209, 96)";
                if(!staffBadgeColor) staffBadgeColor = "#fff";
                const userCardDom = vnode.dom;
                const avatarDom = userCardDom.querySelector('.UserCard-avatar');
                if(!avatarDom) return;
                const avatarStaffElement = document.createElement("div");
                avatarStaffElement.classList.add("ext_staffbadge");
                avatarStaffElement.style.color = staffBadgeColor;
                avatarStaffElement.style.backgroundColor = staffBadgeBg;
                avatarStaffElement.innerText = staffBadgeText;
                avatarDom.append(avatarStaffElement);
            }
        }
    });

    extend(UserCard.prototype, 'infoItems', function(items){
        let user = this.attrs.user;
        if(!user.tagList()) return;

        items.add('tagList',<TagList user={user}/>)
    });

    extend('flarum/common/components/EditUserModal', 'oninit', function () {
        this.status = Stream(this.attrs.user?.staffBadge() || '');
        this.tagList = Stream(this.attrs.user?.tagList() || '');
    });

    extend('flarum/common/components/EditUserModal', 'fields', function (items) {
        if (!app.session.user?.isAdmin()) return;

        items.add('hasbadge',
            <div className="Form-group">
                <label>{app.translator.trans('serakoi-flarumstaffbadge.forum.edit_user.heading')}</label>
                <input className="FormControl"
                    placeholder={extractText(app.translator.trans('serakoi-flarumstaffbadge.forum.edit_user.placeholder'))}
                    bidi={this.status} />
            </div>, 100)
        items.add('tagList',
            <div className="Form-group">
                <label>{app.translator.trans('serakoi-flarumstaffbadge.forum.edit_user.tagList.heading')}</label>
                <input className="FormControl"
                    placeholder={extractText(app.translator.trans('serakoi-flarumstaffbadge.forum.edit_user.tagList.placeholder'))}
                    bidi={this.tagList} />
            </div>, 100)
    });

    extend('flarum/common/components/EditUserModal', 'data', function (data) {
        if (!app.session.user?.isAdmin()) return;

        data.staffBadge = this.status();
        data.tagList = this.tagList();
    });
});
