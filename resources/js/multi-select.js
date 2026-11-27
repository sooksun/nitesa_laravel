export default function multiSelect(options = []) {
    return {
        value: [],
        open: false,
        search: '',
        options,

        get ids() {
            return Array.isArray(this.value) ? this.value : [];
        },

        get selected() {
            return this.ids
                .map((id) => this.options.find((option) => option.id === id))
                .filter(Boolean);
        },

        get filtered() {
            const term = this.search.trim().toLowerCase();
            if (term === '') {
                return this.options;
            }

            return this.options.filter((option) => `${option.code ?? ''} ${option.label}`.toLowerCase().includes(term));
        },

        get summary() {
            return `เลือกแล้ว ${this.selected.length} รายการ`;
        },

        isSelected(id) {
            return this.ids.includes(id);
        },

        toggle(id) {
            this.value = this.isSelected(id)
                ? this.ids.filter((selectedId) => selectedId !== id)
                : [...this.ids, id];
        },

        remove(id) {
            this.value = this.ids.filter((selectedId) => selectedId !== id);
        },

        clear() {
            this.value = [];
        },

        toggleOpen() {
            this.open = !this.open;
            if (this.open) {
                this.search = '';
                this.$nextTick(() => this.$refs.search?.focus({ preventScroll: true }));
            }
        },

        close() {
            this.open = false;
        },
    };
}
