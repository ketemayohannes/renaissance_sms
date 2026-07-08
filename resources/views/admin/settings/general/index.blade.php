<x-admin-layout>
    <x-slot name="header">General Settings</x-slot>

    <div class="py-6" x-data="generalSettings()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── Page Header ── --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">General Settings</h1>
                    <p class="text-slate-500 font-medium mt-1">System configuration, communication channels, and school identity.</p>
                </div>
                {{-- Cache Clear Button --}}
                <form action="{{ route('admin.settings.general.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Clear Cache
                    </button>
                </form>
            </div>

            {{-- ── Flash Messages ── --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm">
                    <p class="font-black text-sm mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Tab Navigation ── --}}
            <div class="flex items-center gap-1 p-1 bg-slate-100/80 rounded-2xl w-fit border border-slate-200/50">
                <template x-for="tab in tabs" :key="tab.id">
                    <button type="button"
                        @click="activeTab = tab.id"
                        :class="activeTab === tab.id
                            ? 'bg-white text-slate-900 shadow-md font-black'
                            : 'text-slate-500 hover:text-slate-700 font-semibold'"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200">
                        <span x-text="tab.icon"></span>
                        <span x-text="tab.label"></span>
                    </button>
                </template>
            </div>

            {{-- ════════════════════════════════════════════════════════════════
                 TAB 1 — Communication
            ════════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'communication'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- Main Form --}}
                    <div class="lg:col-span-2 space-y-6">
                        <form action="{{ route('admin.settings.general.communication.update') }}" method="POST" class="space-y-6" id="comm-form">
                            @csrf

                            {{-- ── Global Channel Toggles ── --}}
                            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                    Global Channel Control
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    {{-- In-App Toggle --}}
                                    <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 cursor-pointer hover:border-violet-200 hover:bg-violet-50/30 transition-all group">
                                        <div>
                                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">🔔 In-App</span>
                                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block">Bell notifications</span>
                                        </div>
                                        <div class="relative">
                                            <input type="hidden" name="in_app_enabled" value="0">
                                            <input type="checkbox" name="in_app_enabled" value="1" x-model="cfg.in_app_enabled" class="sr-only peer">
                                            <div class="w-11 h-6 bg-rose-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                        </div>
                                    </label>
                                    {{-- Email Toggle --}}
                                    <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 cursor-pointer hover:border-teal-200 hover:bg-teal-50/30 transition-all group">
                                        <div>
                                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">📧 Email</span>
                                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block">SMTP / Resend</span>
                                        </div>
                                        <div class="relative">
                                            <input type="hidden" name="email_enabled" value="0">
                                            <input type="checkbox" name="email_enabled" value="1" x-model="cfg.email_enabled" class="sr-only peer">
                                            <div class="w-11 h-6 bg-rose-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                        </div>
                                    </label>
                                    {{-- SMS Toggle --}}
                                    <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 cursor-pointer hover:border-orange-200 hover:bg-orange-50/30 transition-all group">
                                        <div>
                                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">📱 SMS</span>
                                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block">Gateway provider</span>
                                        </div>
                                        <div class="relative">
                                            <input type="hidden" name="sms_enabled" value="0">
                                            <input type="checkbox" name="sms_enabled" value="1" x-model="cfg.sms_enabled" class="sr-only peer">
                                            <div class="w-11 h-6 bg-rose-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- ── Email / Mailer Config ── --}}
                            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50" x-show="cfg.email_enabled" x-collapse>
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-teal-500 rounded-full"></span>
                                    Email Configuration
                                </h3>

                                {{-- Mailer Driver Selector --}}
                                <div class="mb-6">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Mailer Driver</label>
                                    <div class="grid grid-cols-3 gap-4 mt-2">
                                        <template x-for="driver in mailerDrivers" :key="driver.id">
                                            <label :class="cfg.mail_mailer === driver.id ? 'border-teal-400 bg-teal-50/30 ring-1 ring-teal-300' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'"
                                                class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 cursor-pointer transition-all">
                                                <input type="radio" name="mail_mailer" :value="driver.id" x-model="cfg.mail_mailer" class="sr-only">
                                                <span class="text-2xl" x-text="driver.icon"></span>
                                                <span class="text-[11px] font-black text-slate-700 uppercase tracking-wider" x-text="driver.label"></span>
                                                <span class="text-[9px] text-slate-400 font-medium text-center" x-text="driver.desc"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                {{-- Log Driver info --}}
                                <div x-show="cfg.mail_mailer === 'log'" x-collapse class="mb-4 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs font-semibold">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Log driver: emails are NOT sent to recipients. They are written to <code class="font-mono bg-amber-100 px-1 rounded">storage/logs/laravel.log</code> for development/debugging only.</span>
                                </div>

                                {{-- SMTP Fields --}}
                                <div x-show="cfg.mail_mailer === 'smtp'" x-collapse class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMTP Host</label>
                                            <input type="text" name="mail_host" x-model="cfg.mail_host" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="smtp.mailgun.org">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Port</label>
                                            <input type="number" name="mail_port" x-model="cfg.mail_port" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="587">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Encryption</label>
                                            <select name="mail_encryption" x-model="cfg.mail_encryption" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3">
                                                <option value="tls">TLS (recommended)</option>
                                                <option value="ssl">SSL</option>
                                                <option value="null">None</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Username</label>
                                            <input type="text" name="mail_username" x-model="cfg.mail_username" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="postmaster@domain.com">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Password</label>
                                            <input type="password" name="mail_password" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="{{ $settings->mail_password ? '••••••••' : 'Enter password' }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Resend Fields --}}
                                <div x-show="cfg.mail_mailer === 'resend'" x-collapse class="space-y-4">
                                    <div class="flex items-start gap-3 p-4 bg-violet-50 border border-violet-200 rounded-2xl text-violet-800 text-xs font-semibold">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Resend: modern API-first email. Get your free API key at <strong>resend.com</strong> — 3,000 emails/month free, excellent deliverability.</span>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Resend API Key</label>
                                        <input type="password" name="resend_api_key" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="{{ $settings->resend_api_key ? '••••••••' : 're_xxxxxxxxxxxxxxxxxxxx' }}">
                                    </div>
                                </div>

                                {{-- From Address (always shown) --}}
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">From Email Address</label>
                                        <input type="email" name="mail_from_address" x-model="cfg.mail_from_address" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="no-reply@school.edu">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">From Name</label>
                                        <input type="text" name="mail_from_name" x-model="cfg.mail_from_name" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="Renaissance School">
                                    </div>
                                </div>
                            </div>

                            {{-- ── SMS Gateway Config ── --}}
                            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50" x-show="cfg.sms_enabled" x-collapse>
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                                    SMS Gateway Configuration
                                </h3>

                                {{-- Provider Selector --}}
                                <div class="mb-6">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMS Provider</label>
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                        <label :class="cfg.sms_provider === 'africastalking' ? 'border-orange-400 bg-orange-50/30 ring-1 ring-orange-300' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'"
                                            class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all">
                                            <input type="radio" name="sms_provider" value="africastalking" x-model="cfg.sms_provider" class="sr-only">
                                            <span class="text-2xl">🌍</span>
                                            <div>
                                                <span class="text-xs font-black text-slate-700 block">Africa's Talking</span>
                                                <span class="text-[10px] text-slate-400 font-medium">International / East Africa</span>
                                            </div>
                                        </label>
                                        <label :class="cfg.sms_provider === 'smsethiopia' ? 'border-orange-400 bg-orange-50/30 ring-1 ring-orange-300' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'"
                                            class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all">
                                            <input type="radio" name="sms_provider" value="smsethiopia" x-model="cfg.sms_provider" class="sr-only">
                                            <span class="text-2xl">🇪🇹</span>
                                            <div>
                                                <span class="text-xs font-black text-slate-700 block">SMS Ethiopia</span>
                                                <span class="text-[10px] text-slate-400 font-medium">smsethiopia.com</span>
                                            </div>
                                        </label>
                                        <label :class="cfg.sms_provider === 'geezsms' ? 'border-orange-400 bg-orange-50/30 ring-1 ring-orange-300' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'"
                                            class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all">
                                            <input type="radio" name="sms_provider" value="geezsms" x-model="cfg.sms_provider" class="sr-only">
                                            <span class="text-2xl">✉️</span>
                                            <div>
                                                <span class="text-xs font-black text-slate-700 block">GeezSMS</span>
                                                <span class="text-[10px] text-slate-400 font-medium">geezsms.com — Ethiopia</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Africa's Talking Fields --}}
                                <div x-show="cfg.sms_provider === 'africastalking'" x-collapse class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Username</label>
                                            <input type="text" name="africastalking_username" x-model="cfg.africastalking_username" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="sandbox">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Sender ID (Optional)</label>
                                            <input type="text" name="africastalking_from" x-model="cfg.africastalking_from" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="SCHOOL or 24500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">API Key</label>
                                        <input type="password" name="africastalking_api_key" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="{{ $settings->africastalking_api_key ? '••••••••' : 'Enter API key' }}">
                                    </div>
                                    <label class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                        <div>
                                            <span class="text-xs font-bold text-slate-700 block">Sandbox Mode</span>
                                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block">Routes to AT sandbox endpoint — no real SMS sent</span>
                                        </div>
                                        <div class="relative">
                                            <input type="hidden" name="africastalking_sandbox" value="0">
                                            <input type="checkbox" name="africastalking_sandbox" value="1" x-model="cfg.africastalking_sandbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-400"></div>
                                        </div>
                                    </label>
                                </div>

                                {{-- SMS Ethiopia Fields --}}
                                <div x-show="cfg.sms_provider === 'smsethiopia'" x-collapse>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">SMS Ethiopia API Key</label>
                                    <input type="password" name="smsethiopia_api_key" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3 mt-1" placeholder="{{ $settings->smsethiopia_api_key ? '••••••••' : 'Enter API key' }}">
                                </div>

                                {{-- GeezSMS Fields --}}
                                <div x-show="cfg.sms_provider === 'geezsms'" x-collapse class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">API Token</label>
                                            <input type="password" name="geezsms_token" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="{{ $settings->geezsms_token ? '••••••••' : 'Enter API token' }}">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Sender ID (Optional)</label>
                                            <input type="text" name="geezsms_sender_id" value="{{ old('geezsms_sender_id', $settings->geezsms_sender_id) }}" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3" placeholder="e.g. MySchool">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-semibold ml-1">Numbers sent as <span class="font-black text-slate-500">251XXXXXXXXX</span>. Get your token from <a href="https://geezsms.com" target="_blank" class="text-indigo-500 hover:underline">geezsms.com</a>.</p>
                                </div>
                            </div>

                            {{-- Save Button --}}
                            <div class="flex justify-end">
                                <button type="submit" class="px-8 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200/50 active:scale-95 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Communication Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ── Test Panel (sticky sidebar) ── --}}
                    <div class="space-y-6 lg:sticky lg:top-24 self-start">

                        {{-- Test SMS --}}
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-orange-500 rounded-full"></span>
                                Test SMS Delivery
                            </h3>
                            <p class="text-[11px] text-slate-400 mb-4 font-medium">Sends a test message using the currently saved credentials.</p>
                            <form action="{{ route('admin.settings.general.test-sms') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Phone Number</label>
                                    <input type="text" name="phone" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3 mt-1" placeholder="0912345678" required>
                                </div>
                                <button type="submit" class="w-full py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-black text-xs uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-lg shadow-orange-100">
                                    Send Test SMS
                                </button>
                            </form>
                        </div>

                        {{-- Test Email --}}
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-teal-500 rounded-full"></span>
                                Test Email Delivery
                            </h3>
                            <p class="text-[11px] text-slate-400 mb-4 font-medium">Sends a test email using the currently saved configuration.</p>
                            <form action="{{ route('admin.settings.general.test-email') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Recipient Email</label>
                                    <input type="email" name="email" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3 mt-1" placeholder="you@example.com" required>
                                </div>
                                <button type="submit" class="w-full py-3.5 bg-teal-600 hover:bg-teal-700 text-white font-black text-xs uppercase tracking-widest rounded-xl transition-all active:scale-95 shadow-lg shadow-teal-100">
                                    Send Test Email
                                </button>
                            </form>
                        </div>

                        {{-- Status Card --}}
                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 space-y-4">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                Current Status
                            </h3>
                            <div class="space-y-3 text-xs font-semibold">
                                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                    <span class="text-slate-500">In-App Notices</span>
                                    <span :class="cfg.in_app_enabled ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-500'" class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider" x-text="cfg.in_app_enabled ? 'Active' : 'Off'"></span>
                                </div>
                                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                    <span class="text-slate-500">Email Alerts</span>
                                    <span :class="cfg.email_enabled ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-500'" class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider" x-text="cfg.email_enabled ? 'Active (' + cfg.mail_mailer + ')' : 'Off'"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">SMS Alerts</span>
                                    <span :class="cfg.sms_enabled ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500'" class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider" x-text="cfg.sms_enabled ? 'Active (' + cfg.sms_provider + ')' : 'Off'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════
                 TAB 2 — Notification Events
            ════════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'events'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <form action="{{ route('admin.settings.general.events.update') }}" method="POST">
                    @csrf
                    <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden">

                        {{-- Table Header --}}
                        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h2 class="text-base font-black text-slate-800 tracking-tight">Notification Event Routing</h2>
                                <p class="text-xs text-slate-400 font-medium mt-1">Control which channels fire for each system event. Disabled channels override event settings.</p>
                            </div>
                            <button type="submit" class="px-8 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200/50 active:scale-95 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Save Events Routing
                            </button>
                        </div>

                        {{-- Column Headers --}}
                        <div class="grid grid-cols-12 px-8 py-4 bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <div class="col-span-5">Event Category</div>
                            <div class="col-span-2 text-center">🔔 In-App</div>
                            <div class="col-span-2 text-center">📧 Email</div>
                            <div class="col-span-2 text-center">📱 SMS</div>
                            <div class="col-span-1 text-center">SMS Capable</div>
                        </div>

                        {{-- Event Rows --}}
                        <template x-for="event in events" :key="event.id">
                            <div class="grid grid-cols-12 px-8 py-5 border-b border-slate-50 hover:bg-slate-50/50 transition-colors items-center">
                                {{-- Event Info --}}
                                <div class="col-span-5 pr-4">
                                    <h4 class="text-sm font-bold text-slate-800" x-text="event.title"></h4>
                                    <p class="text-[11px] text-slate-400 font-medium mt-1" x-text="event.description"></p>
                                </div>

                                {{-- In-App Toggle --}}
                                <div class="col-span-2 flex justify-center">
                                    <label class="relative inline-flex items-center cursor-pointer" :title="!cfg.in_app_enabled ? 'In-App notifications are globally disabled' : ''">
                                        <input type="hidden" :name="'event_settings[' + event.id + '][in_app]'" value="0">
                                        <input type="checkbox" :name="'event_settings[' + event.id + '][in_app]'" value="1"
                                            x-model="eventSettings[event.id].in_app"
                                            :disabled="!cfg.in_app_enabled"
                                            class="sr-only peer">
                                        <div class="w-11 h-6 bg-rose-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-emerald-500 peer-disabled:bg-slate-200 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"></div>
                                    </label>
                                </div>

                                {{-- Email Toggle --}}
                                <div class="col-span-2 flex justify-center">
                                    <label class="relative inline-flex items-center cursor-pointer" :title="!cfg.email_enabled ? 'Email notifications are globally disabled' : ''">
                                        <input type="hidden" :name="'event_settings[' + event.id + '][email]'" value="0">
                                        <input type="checkbox" :name="'event_settings[' + event.id + '][email]'" value="1"
                                            x-model="eventSettings[event.id].email"
                                            :disabled="!cfg.email_enabled"
                                            class="sr-only peer">
                                        <div class="w-11 h-6 bg-rose-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-emerald-500 peer-disabled:bg-slate-200 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"></div>
                                    </label>
                                </div>

                                {{-- SMS Toggle --}}
                                <div class="col-span-2 flex justify-center">
                                    <div class="relative">
                                        <template x-if="event.allowSms">
                                            <label class="relative inline-flex items-center cursor-pointer" :title="!cfg.sms_enabled ? 'SMS notifications are globally disabled' : ''">
                                                <input type="hidden" :name="'event_settings[' + event.id + '][sms]'" value="0">
                                                <input type="checkbox" :name="'event_settings[' + event.id + '][sms]'" value="1"
                                                    x-model="eventSettings[event.id].sms"
                                                    :disabled="!cfg.sms_enabled"
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-rose-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-emerald-500 peer-disabled:bg-slate-200 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"></div>
                                            </label>
                                        </template>
                                        <template x-if="!event.allowSms">
                                            <div class="w-11 h-6 bg-slate-100 rounded-full cursor-not-allowed opacity-20"></div>
                                        </template>
                                    </div>
                                </div>

                                {{-- SMS badge --}}
                                <div class="col-span-1 flex justify-center">
                                    <span x-show="event.allowSms" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-orange-100 text-orange-600">Yes</span>
                                    <span x-show="!event.allowSms" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">No</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Channel Legend --}}
                    <div class="mt-6 flex flex-wrap gap-4 text-[11px] font-bold text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-violet-400 inline-block"></span> In-App = portal bell notices</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span> Email = delivered via active driver</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span> SMS = sent via active gateway</span>
                    </div>
                </form>
            </div>

            {{-- ════════════════════════════════════════════════════════════════
                 TAB 3 — School Identity
            ════════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'school'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <form action="{{ route('admin.settings.general.school.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            {{-- School Name & Timezone --}}
                            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 space-y-6">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                                    School Identity Settings
                                </h3>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">School Name</label>
                                    <input type="text" name="school_name" value="{{ $schoolName }}" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3 mt-1" placeholder="Renaissance School" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">Timezone
                                        <span class="normal-case font-normal text-slate-400 ml-1">— affects all portal dates, scheduling, and logging</span>
                                    </label>
                                    <select name="school_timezone" class="w-full bg-slate-50 border-slate-100 rounded-xl font-bold text-sm text-slate-600 focus:ring-slate-200 p-3 mt-1" required>
                                        @foreach($timezones as $tz)
                                            <option value="{{ $tz }}" {{ $schoolTimezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-slate-400 mt-2 font-medium">Current local time: <strong>{{ now()->timezone($schoolTimezone)->format('D, M j Y — g:i A') }}</strong></p>
                                </div>
                            </div>

                            {{-- Logo Upload --}}
                            <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 space-y-5">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-rose-400 rounded-full"></span>
                                    School Logo
                                </h3>

                                @if($schoolLogoPath)
                                    <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                        <img src="{{ Storage::disk('public')->url($schoolLogoPath) }}" alt="School Logo" class="h-16 w-auto object-contain rounded-xl">
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">Active Identity Logo</p>
                                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                                <input type="checkbox" name="remove_logo" value="1" class="w-4 h-4 rounded border-slate-300 text-rose-500">
                                                <span class="text-xs text-rose-600 font-semibold">Remove this logo</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 block ml-1">{{ $schoolLogoPath ? 'Upload New Logo' : 'Upload Logo' }}</label>
                                    <div class="mt-2 flex justify-center px-6 pt-6 pb-8 border-2 border-slate-200 border-dashed rounded-2xl hover:border-indigo-300 transition-colors">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-slate-300" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <p class="text-xs text-slate-500">PNG, JPG, or SVG — max 2MB</p>
                                            <input type="file" name="school_logo" accept="image/*" class="text-xs text-slate-500 mt-2">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-8 py-4 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200/50 active:scale-95 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save School Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Info Card --}}
                    <div class="space-y-6 lg:sticky lg:top-24 self-start">
                        <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-200">
                            <div class="text-3xl mb-4">🏫</div>
                            <h3 class="font-black text-lg mb-1">{{ $schoolName }}</h3>
                            <p class="text-indigo-200 text-xs font-medium mb-5">School Identity Configuration</p>
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between pb-1 border-b border-indigo-400/30">
                                    <span class="text-indigo-200">Timezone</span>
                                    <span class="font-bold">{{ $schoolTimezone }}</span>
                                </div>
                                <div class="flex justify-between pb-1 border-b border-indigo-400/30">
                                    <span class="text-indigo-200">Local Time</span>
                                    <span class="font-bold">{{ now()->timezone($schoolTimezone)->format('g:i A') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-indigo-200">UTC Offset</span>
                                    <span class="font-bold">{{ now()->timezone($schoolTimezone)->format('P') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/70 backdrop-blur-xl border border-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 text-xs space-y-3 text-slate-600 font-medium">
                            <p class="font-black text-slate-800 text-sm">Why does timezone matter?</p>
                            <ul class="space-y-2 text-slate-500">
                                <li>✓ Attendance timestamps show in local time</li>
                                <li>✓ Absence SMS fires at correct local hour</li>
                                <li>✓ Audit logs show accurate local time</li>
                                <li>✓ Report card dates match school calendar</li>
                                <li>✓ Queue jobs run on school schedule</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    function generalSettings() {
        return {
            activeTab: '{{ request()->get("tab", "communication") }}',

            tabs: [
                { id: 'communication', label: 'Communication', icon: '📡' },
                { id: 'events',        label: 'Notification Events', icon: '🔔' },
                { id: 'school',        label: 'School Identity', icon: '🏫' },
            ],

            mailerDrivers: [
                { id: 'smtp',   label: 'SMTP',   icon: '📨', desc: 'Any SMTP provider' },
                { id: 'resend', label: 'Resend', icon: '⚡', desc: 'API-first, best delivery' },
                { id: 'log',    label: 'Log',    icon: '📄', desc: 'Dev / testing only' },
            ],

            cfg: {
                in_app_enabled: {{ $settings->in_app_enabled ?? true ? 'true' : 'false' }},
                email_enabled:  {{ $settings->email_enabled  ? 'true' : 'false' }},
                sms_enabled:    {{ $settings->sms_enabled    ? 'true' : 'false' }},
                mail_mailer:    "{{ $settings->mail_mailer ?? 'log' }}",
                mail_host:      "{{ $settings->mail_host ?? '' }}",
                mail_port:      "{{ $settings->mail_port ?? '' }}",
                mail_username:  "{{ $settings->mail_username ?? '' }}",
                mail_encryption:"{{ $settings->mail_encryption ?? 'tls' }}",
                mail_from_address:"{{ $settings->mail_from_address ?? '' }}",
                mail_from_name: "{{ $settings->mail_from_name ?? '' }}",
                sms_provider:   "{{ $settings->sms_provider ?? 'africastalking' }}",
                africastalking_username: "{{ $settings->africastalking_username ?? '' }}",
                africastalking_from:     "{{ $settings->africastalking_from ?? '' }}",
                africastalking_sandbox:  {{ $settings->africastalking_sandbox ? 'true' : 'false' }},
            },

            events: [
                { id: 'notice',            title: 'Notice Board Announcements',  description: 'Fires when a new school-wide notice is published.',            allowSms: true  },
                { id: 'absence',           title: 'Student Absence Alerts',       description: 'Notifies guardian when child is marked absent.',               allowSms: true  },
                { id: 'message',           title: 'Chat Portal Messages',         description: 'Notifies user when they receive a new private message.',        allowSms: false },
                { id: 'export',            title: 'Background Export Completed',  description: 'Notifies admin when a bulk export job finishes.',               allowSms: false },
                { id: 'promotion',         title: 'Student Promotion',            description: 'Notifies guardian when student is promoted to next class.',      allowSms: true  },
                { id: 'disciplinary',      title: 'Disciplinary Action Taken',    description: 'Notifies guardian when a disciplinary record is filed.',         allowSms: true  },
                { id: 'report_card_ready', title: 'Report Card Published',        description: 'Notifies guardian when term report cards are available.',        allowSms: true  },
            ],

            eventSettings: {
                notice:            { in_app: {{ ($settings->event_settings['notice']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['notice']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['notice']['sms'] ?? false) ? 'true' : 'false' }} },
                absence:           { in_app: {{ ($settings->event_settings['absence']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['absence']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['absence']['sms'] ?? true) ? 'true' : 'false' }} },
                message:           { in_app: {{ ($settings->event_settings['message']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['message']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['message']['sms'] ?? false) ? 'true' : 'false' }} },
                export:            { in_app: {{ ($settings->event_settings['export']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['export']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['export']['sms'] ?? false) ? 'true' : 'false' }} },
                promotion:         { in_app: {{ ($settings->event_settings['promotion']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['promotion']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['promotion']['sms'] ?? false) ? 'true' : 'false' }} },
                disciplinary:      { in_app: {{ ($settings->event_settings['disciplinary']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['disciplinary']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['disciplinary']['sms'] ?? true) ? 'true' : 'false' }} },
                report_card_ready: { in_app: {{ ($settings->event_settings['report_card_ready']['in_app'] ?? true) ? 'true' : 'false' }}, email: {{ ($settings->event_settings['report_card_ready']['email'] ?? true) ? 'true' : 'false' }}, sms: {{ ($settings->event_settings['report_card_ready']['sms'] ?? false) ? 'true' : 'false' }} },
            },

            init() {
                // Open the tab requested via redirect query param
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                if (tab && this.tabs.find(t => t.id === tab)) {
                    this.activeTab = tab;
                }
            }
        }
    }
    </script>
    @endpush
</x-admin-layout>
