<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Check-in: {{ $event->title }}</h2>
            <a href="{{ route('organizer.events.show', $event) }}" class="text-sm underline text-gray-600">Back to event</a>
        </div>
    </x-slot>

    <style>
        @keyframes popup-shrink { from { width: 100%; } to { width: 0; } }
        @keyframes popup-in { from { transform: scale(.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .popup-card { animation: popup-in .15s ease-out; }
    </style>

    <div class="py-12 max-w-6xl mx-auto sm:px-6 lg:px-8" x-data="checkIn">
        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Scanner --}}
            <div class="bg-white rounded shadow p-6 space-y-5">
                <h3 class="font-semibold text-lg">Scan a ticket</h3>

                <div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span><strong x-text="checkedIn"></strong> of <strong x-text="rows.length"></strong> checked in</span>
                        <span x-text="percent + '%'"></span>
                    </div>
                    <div class="mt-1 h-2 bg-gray-200 rounded">
                        <div class="h-2 bg-gray-800 rounded" :style="`width: ${percent}%`"></div>
                    </div>
                </div>

                {{-- Camera --}}
                <div>
                    <div x-show="scanning" style="display: none;" class="relative">
                        <video x-ref="video" playsinline muted class="w-full rounded bg-black"></video>
                        <div class="absolute inset-0 m-auto w-2/3 aspect-square border-2 border-white rounded-lg pointer-events-none"></div>
                    </div>
                    <canvas x-ref="canvas" class="hidden"></canvas>

                    <div class="flex flex-wrap gap-3 mt-3">
                        <button type="button" x-show="!scanning" x-on:click="startCamera()"
                                class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">Start camera</button>
                        <button type="button" x-show="scanning" style="display: none;" x-on:click="stopCamera()"
                                class="px-4 py-2 border border-gray-800 text-sm rounded-md">Stop camera</button>

                        <button type="button" x-on:click="toggleSound()"
                                class="ms-auto px-3 py-2 border border-gray-300 text-sm rounded-md"
                                x-text="soundOn ? 'Sound on' : 'Sound off'"></button>
                    </div>

                    <p class="mt-2 text-sm text-red-600" x-show="cameraError" style="display: none;" x-text="cameraError"></p>
                </div>

                {{-- Type the code --}}
                <form x-on:submit.prevent="send(code)">
                    <label for="ticket-code" class="text-sm text-gray-600">Or type the ticket code and press Enter</label>
                    <div class="mt-1 flex gap-2">
                        <input id="ticket-code" x-ref="code" x-model="code" type="text" autocomplete="off"
                               autocapitalize="characters" spellcheck="false" placeholder="Ticket code"
                               class="block w-full border-gray-300 rounded-md shadow-sm font-mono uppercase">
                        <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md whitespace-nowrap">Check in</button>
                    </div>
                </form>

                {{-- Result of the last action --}}
                <div x-show="result" style="display: none;" class="rounded-lg border p-4" :class="resultClass()">
                    <p class="font-semibold" x-text="result ? result.title : ''"></p>
                    <p class="text-sm mt-1" x-text="result ? result.detail : ''"></p>
                </div>
            </div>

            {{-- Attendees --}}
            <div class="bg-white rounded shadow p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-semibold text-lg">Attendees</h3>
                    <input x-model="search" type="search" placeholder="Search name or code"
                           class="border-gray-300 rounded-md shadow-sm text-sm">
                </div>

                <div class="mt-4 max-h-[32rem] overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="py-2">Name</th>
                                <th class="py-2">Ticket</th>
                                <th class="py-2">Status</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in visibleRows" :key="row.id">
                                <tr class="border-t">
                                    <td class="py-2 pe-2">
                                        <div class="font-medium" x-text="row.name"></div>
                                        <div class="text-xs text-gray-500" x-text="row.category"></div>
                                    </td>
                                    <td class="py-2 pe-2 font-mono text-xs" x-text="row.code"></td>
                                    <td class="py-2 pe-2">
                                        <span x-show="row.at" style="display: none;"
                                              class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded whitespace-nowrap"
                                              x-text="row.at"></span>
                                        <span x-show="!row.at" class="text-xs text-gray-400">Not yet</span>
                                    </td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <button type="button" x-show="!row.at" x-on:click="send(row.code)"
                                                class="text-xs text-indigo-600 underline">Check in</button>
                                        <button type="button" x-show="row.at" style="display: none;" x-on:click="undo(row)"
                                                class="text-xs text-red-600 underline">Undo</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <p x-show="rows.length === 0" class="py-6 text-center text-sm text-gray-500">No one has registered yet.</p>
                </div>
            </div>
        </div>

        {{-- Pop-up after every scan --}}
        <template x-if="popup">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                 x-on:click="closePopup()" x-on:keydown.escape.window="closePopup()">

                <div class="popup-card w-full max-w-sm overflow-hidden rounded-2xl bg-white text-center shadow-2xl" x-on:click.stop>
                    <div class="px-6 pb-6 pt-8">
                        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full" :class="popupTheme.icon">
                            <svg x-show="popup.status === 'checked_in'" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="popup.status === 'already'" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg x-show="popup.status !== 'checked_in' && popup.status !== 'already'" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>

                        <h3 class="mt-5 text-2xl font-bold text-gray-900" x-text="popup.title"></h3>

                        <p class="mt-2 text-xl font-semibold text-gray-800" x-show="popup.name" x-text="popup.name"></p>
                        <p class="text-sm text-gray-500" x-show="popup.category" x-text="popup.category"></p>
                        <p class="mt-1 text-sm text-gray-600" x-show="popup.detail" x-text="popup.detail"></p>
                        <p class="mt-3 font-mono text-sm tracking-widest text-gray-500" x-show="popup.code" x-text="popup.code"></p>
                        <p class="mt-1 text-xs text-gray-500" x-show="popup.time"
                           x-text="(popup.status === 'already' ? 'First checked in ' : 'At ') + popup.time"></p>
                    </div>

                    <div class="h-1.5 bg-gray-100">
                        <div class="h-1.5" :class="popupTheme.bar"
                             :style="`animation: popup-shrink ${popup.duration}s linear forwards`"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkIn', () => ({
                rows: @json($rows),
                code: '',
                search: '',
                result: null,
                popup: null,
                popupTimer: null,
                busy: false,
                scanning: false,
                cameraError: '',
                stream: null,
                lastCode: '',
                lastAt: 0,
                soundOn: true,
                audio: null,
                checkInUrl: @json(route('organizer.events.checkin.store', $event)),
                undoUrl: @json(route('organizer.events.checkin.undo', $event)),
                csrf: @json(csrf_token()),

                init() {
                    try {
                        this.soundOn = localStorage.getItem('checkin-sound') !== 'off';
                    } catch (error) {}
                },

                get checkedIn() {
                    return this.rows.filter(row => row.at).length;
                },

                get percent() {
                    return this.rows.length ? Math.round(this.checkedIn / this.rows.length * 100) : 0;
                },

                get visibleRows() {
                    const q = this.search.trim().toLowerCase();
                    if (!q) return this.rows;

                    return this.rows.filter(row =>
                        row.name.toLowerCase().includes(q) || row.code.toLowerCase().includes(q)
                    );
                },

                get popupTheme() {
                    const status = this.popup ? this.popup.status : '';

                    if (status === 'checked_in') return { icon: 'bg-green-100 text-green-600', bar: 'bg-green-500' };
                    if (status === 'already') return { icon: 'bg-amber-100 text-amber-600', bar: 'bg-amber-500' };

                    return { icon: 'bg-red-100 text-red-600', bar: 'bg-red-500' };
                },

                resultClass() {
                    const status = this.result ? this.result.status : '';

                    if (status === 'checked_in') return 'bg-green-50 border-green-300 text-green-900';
                    if (status === 'already') return 'bg-amber-50 border-amber-300 text-amber-900';
                    if (status === 'undone') return 'bg-gray-50 border-gray-300 text-gray-800';

                    return 'bg-red-50 border-red-300 text-red-900';
                },

                // ---------- Sound ----------

                // Browsers only allow sound after the person clicked or typed something on the page
                unlockAudio() {
                    if (!this.audio) {
                        const Context = window.AudioContext || window.webkitAudioContext;
                        if (!Context) return;
                        this.audio = new Context();
                    }

                    if (this.audio.state === 'suspended') this.audio.resume();
                },

                toggleSound() {
                    this.soundOn = !this.soundOn;

                    try {
                        localStorage.setItem('checkin-sound', this.soundOn ? 'on' : 'off');
                    } catch (error) {}

                    // Play a sample so you can hear that it works
                    if (this.soundOn) this.beep('checked_in');
                },

                tone(frequency, start, duration, type = 'sine', volume = 0.25) {
                    const context = this.audio;
                    const oscillator = context.createOscillator();
                    const gain = context.createGain();
                    const begin = context.currentTime + start;

                    oscillator.type = type;
                    oscillator.frequency.value = frequency;

                    gain.gain.setValueAtTime(0.0001, begin);
                    gain.gain.exponentialRampToValueAtTime(volume, begin + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, begin + duration);

                    oscillator.connect(gain);
                    gain.connect(context.destination);
                    oscillator.start(begin);
                    oscillator.stop(begin + duration + 0.05);
                },

                beep(status) {
                    if (!this.soundOn) return;

                    this.unlockAudio();
                    if (!this.audio) return;

                    if (status === 'checked_in') {
                        // Success: two rising notes
                        this.tone(880, 0, 0.12);
                        this.tone(1320, 0.13, 0.2);
                    } else if (status === 'already') {
                        // Warning: two flat notes
                        this.tone(660, 0, 0.14, 'triangle');
                        this.tone(660, 0.2, 0.14, 'triangle');
                    } else {
                        // Not found or error: one low buzz
                        this.tone(200, 0, 0.35, 'sawtooth', 0.2);
                    }
                },

                // ---------- Pop-up ----------

                async showPopup(data) {
                    clearTimeout(this.popupTimer);

                    // Remove the old pop-up first, so the bar and animation start again
                    this.popup = null;
                    await this.$nextTick();

                    data.duration = data.status === 'checked_in' ? 2.5 : 4;
                    this.popup = data;
                    this.popupTimer = setTimeout(() => this.closePopup(), data.duration * 1000);
                },

                closePopup() {
                    clearTimeout(this.popupTimer);
                    this.popup = null;
                },

                // ---------- Talking to the server ----------

                async post(url, body) {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify(body),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!data.status) {
                            return {
                                status: 'error',
                                title: 'Something went wrong',
                                detail: data.message || 'Refresh the page and try again.',
                            };
                        }

                        return data;
                    } catch (error) {
                        return {
                            status: 'error',
                            title: 'No connection',
                            detail: 'Could not reach the server. Check that it is running.',
                        };
                    }
                },

                async send(code) {
                    code = (code || '').trim().toUpperCase();
                    if (!code || this.busy) return;

                    this.unlockAudio();
                    this.busy = true;

                    const data = await this.post(this.checkInUrl, { code: code });
                    this.result = data;

                    const ticket = data.ticket_code || code;
                    const row = this.rows.find(r => r.code === ticket);

                    if ((data.status === 'checked_in' || data.status === 'already') && row) {
                        row.at = data.checked_in_at;
                    }

                    const failed = data.status !== 'checked_in' && data.status !== 'already';

                    this.beep(data.status);
                    this.showPopup({
                        status: data.status,
                        title: data.title,
                        name: row ? row.name : (data.status === 'checked_in' ? data.detail : ''),
                        category: row ? row.category : '',
                        code: ticket,
                        time: data.checked_in_at || '',
                        detail: failed ? data.detail : '',
                    });

                    this.code = '';
                    this.busy = false;
                    this.$refs.code.focus();
                },

                async undo(row) {
                    const data = await this.post(this.undoUrl, { registration_id: row.id });
                    this.result = data;

                    if (data.status === 'undone') row.at = null;
                },

                // ---------- Camera ----------

                async startCamera() {
                    this.cameraError = '';
                    this.unlockAudio();

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.cameraError = 'The camera only works on http://localhost or https. Type the ticket code instead.';
                        return;
                    }

                    try {
                        const jsQR = await window.loadJsQR();
                        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });

                        this.stream = stream;
                        this.scanning = true;
                        await this.$nextTick();

                        const video = this.$refs.video;
                        video.srcObject = stream;
                        await video.play();

                        this.scanLoop(jsQR);
                    } catch (error) {
                        this.stopCamera();
                        this.cameraError = 'Could not open the camera. Allow camera access in the browser, or type the ticket code instead.';
                    }
                },

                stopCamera() {
                    this.scanning = false;

                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                    }
                },

                scanLoop(jsQR) {
                    if (!this.scanning) return;

                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;

                    if (video.readyState === video.HAVE_ENOUGH_DATA && video.videoWidth > 0) {
                        const scale = Math.min(1, 640 / video.videoWidth);
                        canvas.width = Math.round(video.videoWidth * scale);
                        canvas.height = Math.round(video.videoHeight * scale);

                        const context = canvas.getContext('2d', { willReadFrequently: true });
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);

                        const image = context.getImageData(0, 0, canvas.width, canvas.height);
                        const found = jsQR(image.data, image.width, image.height, { inversionAttempts: 'dontInvert' });

                        if (found && found.data) {
                            const now = Date.now();

                            // The same QR code in front of the camera counts only once every 4 seconds
                            if (found.data !== this.lastCode || now - this.lastAt > 4000) {
                                this.lastCode = found.data;
                                this.lastAt = now;
                                this.send(found.data);
                            }
                        }
                    }

                    setTimeout(() => this.scanLoop(jsQR), 200);
                },
            }));
        });
    </script>
</x-app-layout>