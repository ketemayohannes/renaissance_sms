<script>
    // Shows the Ethiopian equivalent of the chosen Gregorian dates, using the server's
    // converter (andegna/calender) so there is a single conversion implementation.
    function ethDates(start, end) {
        return {
            start: start, end: end, startEth: '', endEth: '',
            init() { this.convert(); },
            async label(date) {
                if (!date) return '';
                try {
                    const res = await fetch(`{{ route('academic-calendar.context') }}?date=${encodeURIComponent(date)}`, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return '';
                    const ctx = await res.json();
                    let text = ctx.ethiopianDate.formatted + ' E.C.';
                    if (ctx.quarter) text += ` · Q${ctx.quarter}` + (ctx.academicWeek ? ` W${ctx.academicWeek}` : '');
                    if (!ctx.isSchoolDay) text += ` · not a school day (${ctx.dayTypeReason || ctx.dayType})`;
                    return text;
                } catch (e) { return ''; }
            },
            async convert() {
                this.startEth = await this.label(this.start);
                this.endEth = await this.label(this.end);
            },
        };
    }
</script>
