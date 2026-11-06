{{-- Behaviour for partials.gradebook.score-grid (row totals, unsaved-changes warning, import file name). --}}
<script>
    // Alpine.js component for gradebook state
    function gradebookState() {
        return {
            hasUnsavedChanges: false,
            isSaving: false,
            importFileName: '',
            
            init() {
                // Track changes on all inputs
                this.$nextTick(() => {
                    const form = document.getElementById('gradeForm');
                    if (form) {
                        form.querySelectorAll('input[type="text"]').forEach(input => {
                            input.addEventListener('change', () => {
                                this.hasUnsavedChanges = true;
                            });
                        });
                    }
                });
                
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges && !this.isSaving) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            }
        }
    }

    // Grade Import File Display
    document.addEventListener('DOMContentLoaded', function() {
        const gradeFileInput = document.querySelector('input[name="file"][accept=".csv,.txt"]');
        const placeholder = document.getElementById('grade-import-placeholder');
        const fileNameDisplay = document.getElementById('grade-import-filename');

        if (gradeFileInput && placeholder && fileNameDisplay) {
            gradeFileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    const name = e.target.files[0].name;
                    fileNameDisplay.textContent = name;
                    fileNameDisplay.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                } else {
                    fileNameDisplay.textContent = '';
                    fileNameDisplay.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                }
            });
        }
    });

    function calculateRow(row, skipDirty = false) {
        const inputs = row.querySelectorAll('.mark-input');
        let grandTotal = 0;
        let caTotal = 0;
        let hasAnyValue = false;
        
        inputs.forEach(input => {
            const max = parseFloat(input.getAttribute('max'));
            const isFinal = input.getAttribute('data-is-final') === '1';
            
            if (input.value.trim() !== '') {
                hasAnyValue = true;
            }
            
            let val = parseFloat(input.value) || 0;
            
            // Reset validation styles
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-500');
            
            if (val > max) {
                // Warning style instead of auto-correct
                input.classList.add('border-red-500', 'bg-red-50', 'text-red-600', 'focus:border-red-500', 'focus:ring-red-500');
            }
            
            if (val < 0) {
                val = 0;
                input.value = 0;
            }
            
            grandTotal += val;
            if (!isFinal) {
                caTotal += val;
            }
        });
        
        // Update CA and Total
        const caEl = row.querySelector('.student-ca');
        if (caEl) caEl.innerText = caTotal.toFixed(2);

        const totalEl = row.querySelector('.student-total');
        if (totalEl) {
            if (!hasAnyValue && totalEl.hasAttribute('data-master-total')) {
                const masterTotal = parseFloat(totalEl.getAttribute('data-master-total')) || 0;
                if (masterTotal > 0) {
                    totalEl.innerText = masterTotal.toFixed(2);
                    return; // Keep master total, don't flag as unsaved unless changed
                }
            }
            totalEl.innerText = grandTotal.toFixed(2);
        }
        
        // Mark as unsaved
        if (!skipDirty) {
            const rootElement = document.querySelector('.space-y-8');
            if (rootElement && window.Alpine) {
                const data = Alpine.$data(rootElement);
                if (data) data.hasUnsavedChanges = true;
            }
            // Live progress update on every change
            updateGradingProgress();
        }
    }

    function clearGradebook() {
        document.querySelectorAll('.mark-input').forEach(input => {
            input.value = '';
        });
        document.querySelectorAll('.student-row').forEach(row => {
            calculateRow(row);
        });
    }

    function confirmClear() {
        window.confirmUI({
            type: 'danger',
            title: 'Clear All Marks',
            message: 'Are you sure you want to clear ALL entered marks in this gradebook? This action cannot be undone.',
            buttonText: 'Yes, Clear All',
            callback: () => {
                clearGradebook();
            }
        });
    }
    
    const TOTAL_STUDENTS = {{ $totalStudents }};
    const COMPONENTS_PER_STUDENT = {{ $gradeComponents->count() }};

    function updateGradingProgress() {
        let fullyGraded = 0;
        let totalInputs = 0;
        let filledInputs = 0;
        
        document.querySelectorAll('.student-row').forEach(row => {
            const inputs = row.querySelectorAll('.mark-input');
            totalInputs += inputs.length;
            
            let studentAllFilled = inputs.length > 0;
            inputs.forEach(inp => {
                if (inp.value.trim() !== '') {
                    filledInputs++;
                } else {
                    studentAllFilled = false;
                }
            });
            
            if (studentAllFilled) fullyGraded++;
        });

        const ungraded = TOTAL_STUDENTS - fullyGraded;
        const pct = totalInputs > 0 ? Math.round((filledInputs / totalInputs) * 100) : 0;

        // Update ungraded counter
        const ungradedEl = document.getElementById('ungradedCount');
        if (ungradedEl) ungradedEl.textContent = ungraded + ' Ungraded';

        // Update progress label
        const labelEl = document.getElementById('progressLabel');
        if (labelEl) labelEl.innerHTML = fullyGraded + ' <span class="text-slate-400">/ ' + TOTAL_STUDENTS + '</span>';

        // Update progress bar
        const barEl = document.getElementById('progressBar');
        if (barEl) {
            barEl.style.width = pct + '%';
            if (pct === 100) {
                barEl.classList.remove('from-indigo-500', 'to-indigo-600');
                barEl.classList.add('bg-emerald-500');
            } else {
                barEl.classList.add('from-indigo-500', 'to-indigo-600');
                barEl.classList.remove('bg-emerald-500');
            }
        }
    }
    
    // Keyboard navigation
    document.addEventListener('DOMContentLoaded', function() {
        // Initial calculation (skipping dirty flag)
        document.querySelectorAll('.student-row').forEach(row => {
            calculateRow(row, true);
        });

        // Initial progress sync
        updateGradingProgress();

        const table = document.querySelector('table');
        if (!table) return;
        
        table.addEventListener('keydown', function(e) {
            if (e.target.tagName !== 'INPUT') return;
            
            const cell = e.target.closest('td');
            const row = cell.closest('tr');
            const rows = Array.from(table.querySelectorAll('tbody tr.student-row'));
            const rowIndex = rows.indexOf(row);
            const allCellsInRow = Array.from(row.querySelectorAll('td'));
            const cellIndex = allCellsInRow.indexOf(cell);
            
            let nextInput = null;
            
            if (e.key === 'Enter' || e.key === 'ArrowDown') {
                const nextRow = rows[rowIndex + 1];
                if (nextRow) {
                    nextInput = nextRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                    if (nextInput) e.preventDefault();
                }
            } else if (e.key === 'ArrowUp') {
                const prevRow = rows[rowIndex - 1];
                if (prevRow) {
                    nextInput = prevRow.querySelectorAll('td')[cellIndex]?.querySelector('input');
                    if (nextInput) e.preventDefault();
                }
            } else if (e.key === 'ArrowRight') {
                if (e.target.value === '' || e.target.selectionEnd === e.target.value.length) {
                    const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                    const inputIndex = rowInputs.indexOf(e.target);
                    nextInput = rowInputs[inputIndex + 1];
                    if (nextInput) e.preventDefault();
                }
            } else if (e.key === 'ArrowLeft') {
                if (e.target.value === '' || e.target.selectionStart === 0) {
                    const rowInputs = Array.from(row.querySelectorAll('.mark-input'));
                    const inputIndex = rowInputs.indexOf(e.target);
                    nextInput = rowInputs[inputIndex - 1];
                    if (nextInput) e.preventDefault();
                }
            }
            
            if (nextInput) {
                nextInput.focus();
                nextInput.select();
            }
        });
    });
</script>
