<div x-data="confirmModal()" 
     x-on:confirm-action.window="openModal($event.detail)"
     x-show="show" 
     x-cloak
     class="fixed inset-0 z-[100] overflow-y-auto" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
             aria-hidden="true"
             @click="closeModal()"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-[2.5rem] text-left overflow-hidden shadow-2xl border border-white transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-10">
            
            <div class="text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Icon Bubble -->
                    <div class="flex-shrink-0 flex items-center justify-center h-20 w-20 rounded-[2rem] shadow-xl shadow-indigo-100/50"
                         :class="{
                             'vibrant-gradient-rose text-white': type === 'danger',
                             'vibrant-gradient-emerald text-white': type === 'success',
                             'vibrant-gradient-amber text-white': type === 'warning',
                             'vibrant-gradient-blue text-white shadow-indigo-200': type === 'info'
                         }">
                        <svg x-show="type === 'danger'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <svg x-show="type === 'success'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg x-show="type === 'warning'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <svg x-show="type === 'info'" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    
                    <div class="flex-1">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight" x-text="title"></h3>
                        <p class="mt-3 text-base text-slate-500 font-medium leading-relaxed" x-text="message"></p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-12 flex flex-col sm:flex-row-reverse gap-4">
                <button type="button" 
                        @click="confirmAction()"
                        class="w-full inline-flex justify-center items-center px-8 py-5 rounded-[1.5rem] text-lg font-black text-white shadow-2xl transition-all hover:scale-[1.03] active:scale-95 group"
                        :class="{
                            'vibrant-gradient-rose shadow-rose-200': type === 'danger',
                            'vibrant-gradient-emerald shadow-emerald-200': type === 'success',
                            'vibrant-gradient-amber shadow-amber-200': type === 'warning',
                            'vibrant-gradient-blue shadow-blue-200': type === 'info'
                        }">
                    <span x-text="confirmButtonText"></span>
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
                <button type="button" 
                        x-show="showCancel"
                        @click="closeModal()"
                        class="w-full inline-flex justify-center items-center px-8 py-5 rounded-[1.5rem] bg-slate-50 text-base font-bold text-slate-400 border border-slate-100 transition-all hover:bg-slate-100 hover:text-slate-600 active:scale-95"
                        x-text="cancelButtonText">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.confirmModal = function() {
    return {
        show: false,
        type: 'danger',
        title: '',
        message: '',
        confirmButtonText: '',
        showCancel: true,
        cancelButtonText: 'Not now',
        formToSubmit: null,
        callback: null,

        openModal(detail) {
            this.type = detail.type || 'danger';
            this.message = detail.message || 'Are you sure you want to proceed?';
            this.formToSubmit = detail.form;
            this.callback = detail.callback;
            this.showCancel = detail.showCancel !== undefined ? detail.showCancel : true;
            this.cancelButtonText = detail.cancelButtonText || 'Not now';
            
            const titles = {
                danger: 'Critical Action',
                success: 'Confirm Completion',
                warning: 'Wait a moment',
                info: 'System Note'
            };
            this.title = detail.title || titles[this.type];
            
            const buttonTexts = {
                danger: 'Continue Anyway',
                success: 'Yes, Proceed',
                warning: 'Understand, Continue',
                info: 'Confirmed'
            };
            this.confirmButtonText = detail.buttonText || buttonTexts[this.type];
            
            this.show = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.show = false;
            this.formToSubmit = null;
            this.callback = null;
            document.body.style.overflow = '';
        },

        confirmAction() {
            if (this.formToSubmit) {
                this.formToSubmit.removeEventListener('submit', this.formToSubmit._confirmHandler);
                this.formToSubmit.submit();
            } else if (this.callback && typeof this.callback === 'function') {
                this.callback();
            }
            this.closeModal();
        }
    }
}

window.confirmUI = function(options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    window.dispatchEvent(new CustomEvent('confirm-action', {
        detail: options
    }));
};
</script>

<style>
[x-cloak] { display: none !important; }
</style>
