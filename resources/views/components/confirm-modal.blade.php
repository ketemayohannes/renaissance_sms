<div x-data="confirmModal()" 
     x-on:confirm-action.window="console.log('Modal received confirm-action', $event.detail); openModal($event.detail)"
     x-show="show" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     style="display: none;">
    
    <!-- Background overlay -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             aria-hidden="true"
             @click="closeModal()"></div>

        <!-- Center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Icon - Dynamic based on type -->
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                         :class="{
                             'bg-red-100': type === 'danger',
                             'bg-green-100': type === 'success',
                             'bg-yellow-100': type === 'warning',
                             'bg-blue-100': type === 'info'
                         }">
                        <!-- Danger Icon (Exclamation Triangle) -->
                        <svg x-show="type === 'danger'" class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <!-- Success Icon (Check Circle) -->
                        <svg x-show="type === 'success'" class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Warning Icon (Exclamation) -->
                        <svg x-show="type === 'warning'" class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <!-- Info Icon (Information Circle) -->
                        <svg x-show="type === 'info'" class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="title"></h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" x-text="message"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        @click="confirmAction()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus:ring-red-500': type === 'danger',
                            'bg-green-600 hover:bg-green-700 focus:ring-green-500': type === 'success',
                            'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500': type === 'warning',
                            'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': type === 'info'
                        }"
                        x-text="confirmButtonText">
                </button>
                <button type="button" 
                        x-show="showCancel"
                        @click="closeModal()"
                        @keydown.escape="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        x-text="cancelButtonText">
                </button>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
window.confirmModal = function() {
    console.log('confirmModal initializing');
    return {
        show: false,
        type: 'danger', // danger, success, warning, info
        title: '',
        message: '',
        confirmButtonText: '',
        showCancel: true,
        cancelButtonText: 'Cancel',
        formToSubmit: null,

        openModal(detail) {
            console.log('openModal called', detail);
            this.type = detail.type || 'danger';
            this.message = detail.message || 'Are you sure you want to proceed?';
            this.formToSubmit = detail.form;
            this.showCancel = detail.showCancel !== undefined ? detail.showCancel : true;
            this.cancelButtonText = detail.cancelButtonText || 'Cancel';
            
            // Set title based on type
            const titles = {
                danger: 'Confirm Deletion',
                success: 'Confirm Action',
                warning: 'Warning',
                info: 'Confirmation Required'
            };
            this.title = detail.title || titles[this.type];
            
            // Set button text based on type
            const buttonTexts = {
                danger: 'Delete',
                success: 'Confirm',
                warning: 'Proceed',
                info: 'Confirm'
            };
            this.confirmButtonText = detail.buttonText || buttonTexts[this.type];
            
            this.show = true;
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.show = false;
            this.formToSubmit = null;
            document.body.style.overflow = '';
        },

        confirmAction() {
            if (this.formToSubmit) {
                // Remove the event listener temporarily to avoid infinite loop
                this.formToSubmit.removeEventListener('submit', this.formToSubmit._confirmHandler);
                this.formToSubmit.submit();
            }
            this.closeModal();
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
