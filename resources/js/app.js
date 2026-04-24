import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('oneToOneCreateModal', () => ({
    createModalOpen: false,
    memberQuery: '',
    selectedRecipientId: '',
    requestedAt: '',
    meetingMode: 'online',
    members: [],
    filteredMembers: [],
    weekdayLabels: {},

    init() {
        this.members = JSON.parse(this.$el.dataset.members || '[]');
        this.weekdayLabels = JSON.parse(this.$el.dataset.weekdayLabels || '{}');
        this.selectedRecipientId = String(this.$el.dataset.selectedRecipientId || '');
        this.requestedAt = this.$el.dataset.requestedAt || '';
        this.meetingMode = this.$el.dataset.meetingMode || 'online';

        this.applyMemberFilter();
        this.$watch('memberQuery', () => this.applyMemberFilter());
    },

    normalize(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    },

    applyMemberFilter() {
        const query = this.normalize(this.memberQuery.trim());

        if (!query) {
            this.filteredMembers = this.members;
            return;
        }

        this.filteredMembers = this.members.filter((member) =>
            [member.name, member.email, member.company, member.city]
                .some((value) => this.normalize(value).includes(query))
        );
    },

    get selectedMember() {
        return this.members.find((member) => String(member.id) === this.selectedRecipientId) || null;
    },

    selectMember(id) {
        this.selectedRecipientId = String(id);
    },

    nextOccurrenceFor(slot) {
        const now = new Date();
        const currentWeekday = ((now.getDay() + 6) % 7) + 1;
        let deltaDays = slot.weekday - currentWeekday;

        if (deltaDays < 0) {
            deltaDays += 7;
        }

        const [hours, minutes] = slot.starts_at.split(':').map(Number);
        const candidate = new Date(now);
        candidate.setHours(hours, minutes, 0, 0);
        candidate.setDate(candidate.getDate() + deltaDays);

        if (deltaDays === 0 && candidate <= now) {
            candidate.setDate(candidate.getDate() + 7);
        }

        return candidate;
    },

    toDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    },

    chooseSlot(slot) {
        this.meetingMode = slot.meeting_mode;
        this.requestedAt = this.toDateTimeLocal(this.nextOccurrenceFor(slot));
    },
}));

Alpine.start();
