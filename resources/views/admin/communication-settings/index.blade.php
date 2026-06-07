<x-admin-layout>
    <x-slot name="header">Communication Settings</x-slot>

    <div class="py-6" x-data="communicationConfig()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Notifications (Handled globally by admin-layout.blade.php) -->

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl space-y-1 shadow-sm font-semibold text-sm">
                    <div class="flex items-center gap-2 text-rose-900 font-bold mb-1">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Please fix the following validation errors:</span>
                    </div>
                    <ul class="list-disc list-inside pl-4 text-xs font-medium text-rose-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Communication Configuration</h1>
                    <p class="text-slate-500 font-medium">Manage SMS Gateway configurations, SMTP details, and alert behaviors.</p>
                </div>
                <button type="submit" form="communication-settings-form" class="px-8 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200/50 active:scale-95">
                    Save Changes
                </button>
            </div>

            <!-- Dashboard Content Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Form (Col 1 & 2) -->
                <div class="lg:col-span-2 space-y-8">
                    <form id="communication-settings-form" action="{{ route('admin.settings.communication.update') }}" method="POST" class="space-y-8">
                        @csrf
                        
                        <!-- Global Activation Card -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                Global Channels Enablement
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email Toggle -->
                                <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-inner transition-all">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-700 uppercase tracking-wider">Email System</span>
                                        <span class="text-[10px] text-slate-400 font-bold mt-1">Activate mail delivery system</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="email_enabled" value="1" x-model="config.email_enabled" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>

                                <!-- SMS Toggle -->
                                <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-inner transition-all">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-700 uppercase tracking-wider">SMS Gateway</span>
                                        <span class="text-[10px] text-slate-400 font-bold mt-1">Activate Africa's Talking API Gateway</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="sms_enabled" value="1" x-model="config.sms_enabled" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Gateway Credentials (Africa's Talking / SMS Ethiopia) -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50" x-show="config.sms_enabled" x-collapse>
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                                SMS Gateway Configuration
                            </h3>
                            
                            <div class="space-y-6">
                                <!-- SMS Provider Selector -->
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMS Gateway Provider</label>
                                    <select name="sms_provider" x-model="config.sms_provider" class="w-full bg-slate-50 border-slate-100 rounded-xl text-sm font-bold text-slate-600 focus:ring-slate-200 focus:border-slate-200">
                                        <option value="africastalking">Africa's Talking (International / East Africa)</option>
                                        <option value="smsethiopia">SMS Ethiopia (smsethiopia.com)</option>
                                    </select>
                                </div>

                                <!-- Africa's Talking Block -->
                                <div class="space-y-4" x-show="config.sms_provider === 'africastalking'" x-collapse>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Username</label>
                                            <input type="text" name="africastalking_username" x-model="config.africastalking_username" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="e.g. sandbox or schoolname">
                                        </div>

                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Sender ID / Shortcode (Optional)</label>
                                            <input type="text" name="africastalking_from" x-model="config.africastalking_from" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="e.g. 24500 or MySchool">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">API Key</label>
                                        <input type="password" name="africastalking_api_key" x-model="config.africastalking_api_key" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="••••••••••••••••••••••••••••••••••••">
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/50">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-600">Sandbox Environment</span>
                                            <span class="text-[9px] text-slate-400 font-bold">Forces API routing to sandbox endpoint</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="africastalking_sandbox" value="1" x-model="config.africastalking_sandbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-900"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- SMS Ethiopia Block -->
                                <div class="space-y-4" x-show="config.sms_provider === 'smsethiopia'" x-collapse>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMS Ethiopia API Key</label>
                                        <input type="password" name="smsethiopia_api_key" x-model="config.smsethiopia_api_key" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="••••••••••••••••••••••••••••••••••••">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email SMTP Settings -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50" x-show="config.email_enabled" x-collapse>
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-teal-500 rounded-full"></span>
                                Mailer & SMTP Configurations
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Mailer Driver</label>
                                        <select name="mail_mailer" x-model="config.mail_mailer" class="w-full bg-slate-50 border-slate-100 rounded-xl text-sm font-bold text-slate-600 focus:ring-slate-200 focus:border-slate-200">
                                            <option value="smtp">SMTP Driver (Outgoing Mail)</option>
                                            <option value="log">Log (Testing — write to app logs)</option>
                                        </select>
                                    </div>

                                    <div x-show="config.mail_mailer === 'smtp'">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMTP Host</label>
                                        <input type="text" name="mail_host" x-model="config.mail_host" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="smtp.mailgun.org">
                                    </div>

                                    <div x-show="config.mail_mailer === 'smtp'">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMTP Port</label>
                                        <input type="number" name="mail_port" x-model="config.mail_port" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="587">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-show="config.mail_mailer === 'smtp'">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Encryption Protocol</label>
                                        <select name="mail_encryption" x-model="config.mail_encryption" class="w-full bg-slate-50 border-slate-100 rounded-xl text-sm font-bold text-slate-600 focus:ring-slate-200 focus:border-slate-200">
                                            <option value="tls">TLS (Recommended)</option>
                                            <option value="ssl">SSL</option>
                                            <option value="null">None</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMTP Username</label>
                                        <input type="text" name="mail_username" x-model="config.mail_username" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="postmaster@domain.com">
                                    </div>

                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMTP Password</label>
                                        <input type="password" name="mail_password" x-model="config.mail_password" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="••••••••••••••••••••••••">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Sender Email ("From" Address)</label>
                                        <input type="email" name="mail_from_address" x-model="config.mail_from_address" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="no-reply@school.edu">
                                    </div>

                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Sender Name ("From" Name)</label>
                                        <input type="text" name="mail_from_name" x-model="config.mail_from_name" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="Renaissance School">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Event Subscriptions Card -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                                Global Channel Routings by Event
                            </h3>
                            
                            <div class="divide-y divide-slate-100">
                                <template x-for="event in events" :key="event.id">
                                    <div class="py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800" x-text="event.title"></h4>
                                            <p class="text-xs text-slate-400 font-medium mt-1" x-text="event.description"></p>
                                        </div>
                                        
                                        <div class="flex items-center gap-6">
                                            <!-- Email Toggle for Event -->
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="hidden" :name="`event_settings[${event.id}][email]`" value="0">
                                                <input type="checkbox" :name="`event_settings[${event.id}][email]`" value="1" 
                                                       x-model="config.event_settings[event.id].email" 
                                                       :disabled="!config.email_enabled"
                                                       class="w-4 h-4 rounded border-slate-200 text-slate-900 focus:ring-slate-200 disabled:opacity-40 transition-all">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 group-hover:text-slate-800 transition-colors" :class="{'opacity-40': !config.email_enabled}">Email</span>
                                            </label>

                                            <!-- SMS Toggle for Event (if sms allowed by event) -->
                                            <label class="flex items-center gap-2 cursor-pointer group" x-show="event.allowSms">
                                                <input type="hidden" :name="`event_settings[${event.id}][sms]`" value="0">
                                                <input type="checkbox" :name="`event_settings[${event.id}][sms]`" value="1" 
                                                       x-model="config.event_settings[event.id].sms" 
                                                       :disabled="!config.sms_enabled"
                                                       class="w-4 h-4 rounded border-slate-200 text-slate-900 focus:ring-slate-200 disabled:opacity-40 transition-all">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 group-hover:text-slate-800 transition-colors" :class="{'opacity-40': !config.sms_enabled}">SMS</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Test Connection Panel (Col 3) -->
                <div class="space-y-6">
                    <div class="sticky top-24 space-y-6">
                        
                        <!-- Test SMS Gateway -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                                Test SMS Delivery
                            </h3>
                            <p class="text-xs text-slate-400 font-medium leading-relaxed mb-6">
                                Sends a test message via the active gateway provider (Africa's Talking or SMS Ethiopia) using saved configurations.
                            </p>

                            <form action="{{ route('admin.settings.communication.test-sms') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Phone Number</label>
                                    <input type="text" name="phone" required class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="e.g. 0912345678">
                                </div>
                                <button type="submit" class="w-full py-3 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-all active:scale-95">
                                    Send Test SMS
                                </button>
                            </form>
                        </div>

                        <!-- Test Email Delivery -->
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-teal-500 rounded-full"></span>
                                Test Email Delivery
                            </h3>
                            <p class="text-xs text-slate-400 font-medium leading-relaxed mb-6">
                                Sends an SMTP test email using configurations currently stored.
                            </p>

                            <form action="{{ route('admin.settings.communication.test-email') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Recipient Email</label>
                                    <input type="email" name="email" required class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 focus:border-slate-200" placeholder="e.g. recipient@mail.com">
                                </div>
                                <button type="submit" class="w-full py-3 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-all active:scale-95">
                                    Send Test Email
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function communicationConfig() {
            return {
                config: {
                    sms_enabled: {{ $settings->sms_enabled ? 'true' : 'false' }},
                    sms_provider: "{{ $settings->sms_provider ?? 'africastalking' }}",
                    email_enabled: {{ $settings->email_enabled ? 'true' : 'false' }},
                    africastalking_username: "{{ $settings->africastalking_username ?? '' }}",
                    africastalking_api_key: "{{ $settings->africastalking_api_key ?? '' }}",
                    africastalking_from: "{{ $settings->africastalking_from ?? '' }}",
                    africastalking_sandbox: {{ $settings->africastalking_sandbox ? 'true' : 'false' }},
                    smsethiopia_api_key: "{{ $settings->smsethiopia_api_key ?? '' }}",
                    mail_mailer: "{{ $settings->mail_mailer ?? 'smtp' }}",
                    mail_host: "{{ $settings->mail_host ?? '' }}",
                    mail_port: "{{ $settings->mail_port ?? '' }}",
                    mail_username: "{{ $settings->mail_username ?? '' }}",
                    mail_password: "{{ $settings->mail_password ?? '' }}",
                    mail_encryption: "{{ $settings->mail_encryption ?? 'tls' }}",
                    mail_from_address: "{{ $settings->mail_from_address ?? '' }}",
                    mail_from_name: "{{ $settings->mail_from_name ?? '' }}",
                    event_settings: {
                        notice: {
                            sms: {{ isset($settings->event_settings['notice']['sms']) && $settings->event_settings['notice']['sms'] ? 'true' : 'false' }},
                            email: {{ isset($settings->event_settings['notice']['email']) && $settings->event_settings['notice']['email'] ? 'true' : 'false' }},
                        },
                        absence: {
                            sms: {{ isset($settings->event_settings['absence']['sms']) && $settings->event_settings['absence']['sms'] ? 'true' : 'false' }},
                            email: {{ isset($settings->event_settings['absence']['email']) && $settings->event_settings['absence']['email'] ? 'true' : 'false' }},
                        },
                        message: {
                            sms: {{ isset($settings->event_settings['message']['sms']) && $settings->event_settings['message']['sms'] ? 'true' : 'false' }},
                            email: {{ isset($settings->event_settings['message']['email']) && $settings->event_settings['message']['email'] ? 'true' : 'false' }},
                        },
                        export: {
                            sms: {{ isset($settings->event_settings['export']['sms']) && $settings->event_settings['export']['sms'] ? 'true' : 'false' }},
                            email: {{ isset($settings->event_settings['export']['email']) && $settings->event_settings['export']['email'] ? 'true' : 'false' }},
                        }
                    }
                },
                events: [
                    { id: 'notice', title: 'New Notice Board Announcements', description: 'Fires when a new school-wide notice is posted.', allowSms: true },
                    { id: 'absence', title: 'Student Absence Alerts', description: 'Notifies parent when child is marked absent.', allowSms: true },
                    { id: 'message', title: 'Chat Portal Messages', description: 'Sends email alert when a user gets a new message.', allowSms: false },
                    { id: 'export', title: 'Background Job Exports', description: 'Notifies admins when bulk exports complete.', allowSms: false },
                ]
            }
        }
    </script>
    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @endpush
</x-admin-layout>
