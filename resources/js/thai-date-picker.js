const BUDDHIST_ERA_OFFSET = 543;

const MONTHS = [
    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม',
];

const WEEKDAYS = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

const pad = (number) => String(number).padStart(2, '0');

const isRealDate = (year, month, day) => {
    const date = new Date(year, month, day);

    return year >= 1000
        && date.getFullYear() === year
        && date.getMonth() === month
        && date.getDate() === day;
};

const toIso = ({ year, month, day }) => `${year}-${pad(month + 1)}-${pad(day)}`;

const today = () => {
    const now = new Date();

    return { year: now.getFullYear(), month: now.getMonth(), day: now.getDate() };
};

export function parseIso(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(value ?? '');
    if (!match) {
        return null;
    }

    const parts = { year: Number(match[1]), month: Number(match[2]) - 1, day: Number(match[3]) };

    return isRealDate(parts.year, parts.month, parts.day) ? parts : null;
}

export function formatThai({ year, month, day }) {
    return `${pad(day)}/${pad(month + 1)}/${year + BUDDHIST_ERA_OFFSET}`;
}

// ปี 2 หลักถือเป็น พ.ศ. 25xx, ปีตั้งแต่ 2400 ขึ้นไปถือเป็น พ.ศ., ต่ำกว่านั้นถือเป็น ค.ศ.
export function parseThai(text) {
    const match = /^(\d{1,2})\s*[/.-]\s*(\d{1,2})\s*[/.-]\s*(\d{2}|\d{4})$/.exec(text.trim());
    if (!match) {
        return null;
    }

    let year = Number(match[3]);
    if (match[3].length === 2) {
        year += 2500;
    }
    if (year >= 2400) {
        year -= BUDDHIST_ERA_OFFSET;
    }

    const parts = { year, month: Number(match[2]) - 1, day: Number(match[1]) };

    return isRealDate(parts.year, parts.month, parts.day) ? parts : null;
}

export default function thaiDatePicker() {
    return {
        value: '',
        text: '',
        open: false,
        viewYear: today().year,
        viewMonth: today().month,
        months: MONTHS,
        weekdays: WEEKDAYS,

        init() {
            this.syncFromValue();
            this.$watch('value', () => this.syncFromValue());
        },

        syncFromValue() {
            const parsed = parseIso(this.value);
            const base = parsed ?? today();

            this.text = parsed ? formatThai(parsed) : '';
            this.viewYear = base.year;
            this.viewMonth = base.month;
        },

        get years() {
            const current = today().year;
            const first = Math.min(current - 80, this.viewYear);
            const last = Math.max(current + 20, this.viewYear);

            return Array.from({ length: last - first + 1 }, (_, index) => ({
                value: first + index,
                label: first + index + BUDDHIST_ERA_OFFSET,
            }));
        },

        get cells() {
            const offset = new Date(this.viewYear, this.viewMonth, 1).getDay();
            const count = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const todayIso = toIso(today());
            const selectedIso = parseIso(this.value) ? this.value.slice(0, 10) : '';
            const cells = Array.from({ length: offset }, (_, index) => ({ key: `blank-${index}`, day: null }));

            for (let day = 1; day <= count; day++) {
                const iso = toIso({ year: this.viewYear, month: this.viewMonth, day });
                cells.push({ key: iso, day, iso, isToday: iso === todayIso, isSelected: iso === selectedIso });
            }

            return cells;
        },

        toggle() {
            if (!this.open) {
                this.syncFromValue();
            }
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        shiftMonth(delta) {
            const target = new Date(this.viewYear, this.viewMonth + delta, 1);

            this.viewYear = target.getFullYear();
            this.viewMonth = target.getMonth();
        },

        select(iso) {
            this.value = iso;
            this.close();
        },

        selectToday() {
            this.select(toIso(today()));
        },

        clear() {
            this.value = '';
            this.text = '';
            this.close();
        },

        commitText() {
            if (this.text.trim() === '') {
                this.value = '';

                return;
            }

            const parsed = parseThai(this.text);
            if (parsed) {
                this.value = toIso(parsed);
            }
            this.syncFromValue();
        },
    };
}
